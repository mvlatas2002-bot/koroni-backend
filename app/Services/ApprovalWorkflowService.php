<?php

namespace App\Services;

use App\Models\ApprovalAuthority;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    private const SALES_REP_SELF_APPROVAL_LIMIT = 4.0;
    private const COMMERCIAL_APPROVAL_LIMIT = 15.0;

    public function create(User $actor, array $data): ApprovalRequest
    {
        return DB::transaction(function () use ($actor, $data) {
            $workflowType = $data['workflow_type'];
            $intent = $data['intent'] ?? 'submit';
            $requestDate = Carbon::now();
            $steps = $intent === 'draft' ? [] : $this->resolvePlan($actor, $workflowType, $data);
            $status = $intent === 'draft' ? 'draft' : (count($steps) === 0 ? 'approved' : 'pending');

            $request = ApprovalRequest::create([
                'request_code' => $this->nextRequestCode($workflowType, $requestDate),
                'workflow_type' => $workflowType,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'requester_id' => $actor->id,
                'status' => $status,
                'current_approver_id' => $steps[0]['approver_id'] ?? null,
                'current_step_number' => $steps[0]['step_number'] ?? null,
                'amount' => $data['amount'] ?? null,
                'discount_percent' => $data['discount_percent'] ?? null,
                'starts_on' => $data['starts_on'] ?? null,
                'ends_on' => $data['ends_on'] ?? null,
                'payload' => [
                    'created_by_module' => $this->moduleForWorkflow($workflowType),
                    'approval_plan' => $steps,
                    ...($data['payload'] ?? []),
                ],
                'submitted_at' => $intent === 'draft' ? null : now(),
                'decided_at' => $status === 'approved' ? now() : null,
            ]);

            foreach ($steps as $step) {
                $request->steps()->create($step);
            }

            return $request->load(['requester.role', 'currentApprover.role', 'steps.approver.role']);
        });
    }

    public function decide(User $actor, ApprovalRequest $request, string $decision, ?string $comments = null): ApprovalRequest
    {
        return DB::transaction(function () use ($actor, $request, $decision, $comments) {
            $request = ApprovalRequest::with(['steps' => fn ($query) => $query->orderBy('step_number')])
                ->lockForUpdate()
                ->findOrFail($request->id);

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'Η αίτηση δεν περιμένει πλέον απόφαση.',
                ]);
            }

            if ($request->requester_id === $actor->id) {
                throw ValidationException::withMessages([
                    'decision' => 'Ο δημιουργός της αίτησης δεν μπορεί να εγκρίνει ή να απορρίψει τη δική του αίτηση.',
                ]);
            }

            $activeStep = $request->steps->firstWhere('status', 'pending');

            if (!$activeStep) {
                throw ValidationException::withMessages([
                    'decision' => 'Δεν βρέθηκε ενεργό βήμα έγκρισης.',
                ]);
            }

            if (!$this->canDecide($actor, $activeStep)) {
                throw ValidationException::withMessages([
                    'decision' => 'Δεν έχεις δικαίωμα απόφασης για αυτό το βήμα.',
                ]);
            }

            if ($decision === 'comment') {
                $activeStep->update([
                    'comments' => trim((string) $comments) ?: $activeStep->comments,
                ]);

                return $request->fresh(['requester.role', 'currentApprover.role', 'steps.approver.role']);
            }

            $activeStep->update([
                'approver_id' => $actor->id,
                'status' => $decision === 'approve' ? 'approved' : 'rejected',
                'comments' => $comments,
                'acted_at' => now(),
            ]);

            if ($decision === 'reject') {
                $request->update([
                    'status' => 'rejected',
                    'current_approver_id' => null,
                    'current_step_number' => null,
                    'decided_at' => now(),
                ]);

                return $request->fresh(['requester.role', 'currentApprover.role', 'steps.approver.role']);
            }

            $nextStep = $request->steps()
                ->where('status', 'pending')
                ->orderBy('step_number')
                ->first();

            $request->update([
                'status' => $nextStep ? 'pending' : 'approved',
                'current_approver_id' => $nextStep?->approver_id,
                'current_step_number' => $nextStep?->step_number,
                'decided_at' => $nextStep ? null : now(),
            ]);

            return $request->fresh(['requester.role', 'currentApprover.role', 'steps.approver.role']);
        });
    }

    public function pendingFor(User $user)
    {
        return ApprovalRequest::with(['requester.role', 'currentApprover.role', 'steps.approver.role'])
            ->where('status', 'pending')
            ->where('requester_id', '!=', $user->id)
            ->when(!$this->isSystemAdmin($user), function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery
                        ->where('current_approver_id', $user->id)
                        ->orWhereHas('steps', function ($stepQuery) use ($user) {
                            $stepQuery
                                ->where('status', 'pending')
                                ->whereNull('approver_id')
                                ->where('required_role_code', $user->role?->code);
                        });
                });
            })
            ->latest()
            ->get();
    }

    private function resolvePlan(User $actor, string $workflowType, array $data): array
    {
        return match ($workflowType) {
            'leave' => $this->leavePlan($actor),
            'discount' => $this->discountPlan($actor, (float) ($data['discount_percent'] ?? 0)),
            default => $this->managerPlan($actor),
        };
    }

    private function leavePlan(User $actor): array
    {
        $manager = $actor->actingManager ?: $actor->manager;
        $hrReviewer = $this->firstUserWithRole(['OPERATIONS_ADMIN', 'SYSTEM_ADMIN', 'MANAGEMENT']);

        if (!$manager) {
            throw ValidationException::withMessages([
                'workflow_type' => 'Δεν έχει οριστεί προϊστάμενος για τον χρήστη.',
            ]);
        }

        $steps = [[
            'step_number' => 1,
            'step_type' => 'direct_manager',
            'label' => 'Άμεσος προϊστάμενος',
            'approver_id' => $manager->id,
            'required_role_code' => $manager->role?->code,
            'status' => 'pending',
        ]];

        if ($hrReviewer && $hrReviewer->id !== $manager->id) {
            $steps[] = [
                'step_number' => 2,
                'step_type' => 'hr_review',
                'label' => 'HR / Operations review',
                'approver_id' => $hrReviewer->id,
                'required_role_code' => $hrReviewer->role?->code,
                'status' => 'pending',
            ];
        }

        return $steps;
    }

    private function discountPlan(User $actor, float $discountPercent): array
    {
        if ($discountPercent <= self::SALES_REP_SELF_APPROVAL_LIMIT) {
            return [];
        }

        $rule = $this->matchingDiscountAuthority($actor, $discountPercent);

        if ($rule) {
            $fallbackRole = $rule->required_role_code
                ?? ($rule->authority_type === 'management' ? 'MANAGEMENT' : 'COMMERCIAL_DIRECTOR');
            $approver = $rule->approver && $rule->approver->id !== $actor->id
                ? $rule->approver
                : $this->firstUserWithRole([$fallbackRole, 'MANAGEMENT', 'SYSTEM_ADMIN'], $actor->id);

            return [[
                'step_number' => 1,
                'step_type' => $rule->authority_type,
                'label' => $rule->label ?: $this->labelForDiscountRule($rule->authority_type),
                'approver_id' => $approver?->id,
                'required_role_code' => $fallbackRole,
                'status' => 'pending',
            ]];
        }

        if ($discountPercent < self::COMMERCIAL_APPROVAL_LIMIT) {
            $approver = $this->firstUserWithRole(['COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'SYSTEM_ADMIN'], $actor->id);

            return [[
                'step_number' => 1,
                'step_type' => 'commercial_approval',
                'label' => 'Εμπορική έγκριση',
                'approver_id' => $approver?->id,
                'required_role_code' => 'COMMERCIAL_DIRECTOR',
                'status' => 'pending',
            ]];
        }

        $approver = $this->firstUserWithRole(['MANAGEMENT', 'SYSTEM_ADMIN'], $actor->id);

        return [[
            'step_number' => 1,
            'step_type' => 'management_approval',
            'label' => 'Έγκριση διοίκησης',
            'approver_id' => $approver?->id,
            'required_role_code' => 'MANAGEMENT',
            'status' => 'pending',
        ]];
    }

    private function managerPlan(User $actor): array
    {
        $manager = $actor->actingManager ?: $actor->manager ?: $actor->secondaryApprover;

        if (!$manager) {
            throw ValidationException::withMessages([
                'workflow_type' => 'Δεν έχει οριστεί προϊστάμενος ή secondary approver.',
            ]);
        }

        return [[
            'step_number' => 1,
            'step_type' => 'manager_approval',
            'label' => 'Έγκριση υπευθύνου',
            'approver_id' => $manager->id,
            'required_role_code' => $manager->role?->code,
            'status' => 'pending',
        ]];
    }

    private function matchingDiscountAuthority(User $actor, float $discountPercent): ?ApprovalAuthority
    {
        $today = now()->toDateString();

        return ApprovalAuthority::with(['approver.role'])
            ->where('workflow_type', 'discount')
            ->where('is_active', true)
            ->where(function ($query) use ($actor) {
                $query->whereNull('department_id');

                if ($actor->department_id) {
                    $query->orWhere('department_id', $actor->department_id);
                }
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_from')->orWhere('effective_from', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', $today);
            })
            ->get()
            ->filter(fn (ApprovalAuthority $rule) => $this->percentMatchesRule($discountPercent, $rule))
            ->sortByDesc(fn (ApprovalAuthority $rule) => $rule->department_id === $actor->department_id ? 1 : 0)
            ->first();
    }

    private function percentMatchesRule(float $discountPercent, ApprovalAuthority $rule): bool
    {
        $min = $rule->min_percent !== null ? (float) $rule->min_percent : null;
        $max = $rule->max_percent !== null ? (float) $rule->max_percent : null;

        $matchesMin = $min === null
            || ($rule->min_inclusive ? $discountPercent >= $min : $discountPercent > $min);
        $matchesMax = $max === null
            || ($rule->max_inclusive ? $discountPercent <= $max : $discountPercent < $max);

        return $matchesMin && $matchesMax;
    }

    private function labelForDiscountRule(string $authorityType): string
    {
        return match ($authorityType) {
            'management' => 'Έγκριση διοίκησης',
            'role_based' => 'Έγκριση βάσει ρόλου',
            default => 'Εμπορική έγκριση',
        };
    }

    private function firstUserWithRole(array $roleCodes, ?int $exceptUserId = null): ?User
    {
        foreach ($roleCodes as $roleCode) {
            $user = User::with('role')
                ->where('is_active', true)
                ->when($exceptUserId, fn ($query) => $query->whereKeyNot($exceptUserId))
                ->whereHas('role', fn ($query) => $query->where('code', $roleCode))
                ->orderBy('name')
                ->first();

            if ($user) {
                return $user;
            }
        }

        return null;
    }

    private function canDecide(User $actor, ApprovalStep $step): bool
    {
        if ($this->isSystemAdmin($actor)) {
            return true;
        }

        if ($step->approver_id && $step->approver_id === $actor->id) {
            return true;
        }

        return !$step->approver_id && $step->required_role_code === $actor->role?->code;
    }

    private function isSystemAdmin(User $user): bool
    {
        return in_array($user->role?->code, ['SYSTEM_ADMIN'], true);
    }

    private function nextRequestCode(string $workflowType, Carbon $date): string
    {
        $prefix = match ($workflowType) {
            'leave' => 'LREQ',
            'discount' => 'DREQ',
            default => 'REQ',
        };

        $year = $date->year;
        $count = ApprovalRequest::where('workflow_type', $workflowType)
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->count() + 1;

        return sprintf('%s-%d-%06d', $prefix, $year, $count);
    }

    private function moduleForWorkflow(string $workflowType): string
    {
        return match ($workflowType) {
            'leave' => 'LEAVES',
            'discount' => 'APPROVALS',
            default => 'APPROVALS',
        };
    }
}
