<?php

return [
    'roles' => [
        'STANDARD_USER' => 'Βασικός χρήστης',
        'SALES_REP' => 'Πωλητής',
        'SUPERVISOR' => 'Προϊστάμενος',
        'COMMERCIAL_DIRECTOR' => 'Εμπορικός διευθυντής',
        'MANAGEMENT' => 'Διοίκηση',
        'OPERATIONS_ADMIN' => 'Operations Admin',
        'SYSTEM_ADMIN' => 'System Admin',
    ],

    'role_groups' => [
        'approvers' => ['SUPERVISOR', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'SYSTEM_ADMIN'],
        'people_viewers' => ['SUPERVISOR', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'],
        'management' => ['MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'],
        'sales_program_viewers' => ['SALES_REP', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'],
        'sales_program_managers' => ['COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'],
        'platform_admins' => ['SYSTEM_ADMIN'],
    ],

    'modules' => [
        'ANNOUNCEMENTS' => ['title' => 'Ενημερώσεις', 'summary' => 'Ανακοινώσεις, updates και εταιρικός συντονισμός.', 'category' => 'Core', 'included' => true, 'dependencies' => []],
        'MEETINGS' => ['title' => 'Meetings', 'summary' => 'Συναντήσεις και follow-up ενέργειες.', 'category' => 'Core', 'included' => true, 'dependencies' => []],
        'LEAVES' => ['title' => 'Άδειες', 'summary' => 'Αιτήσεις αδειών, εγκρίσεις και απουσίες ημέρας.', 'category' => 'People', 'included' => true, 'dependencies' => ['ORGANIZATION']],
        'APPROVALS' => ['title' => 'Εγκρίσεις', 'summary' => 'Ροές εγκρίσεων με υπεύθυνο επόμενης απόφασης.', 'category' => 'Approvals', 'included' => true, 'dependencies' => ['ORGANIZATION']],
        'ORGANIZATION' => ['title' => 'Οργάνωση', 'summary' => 'Οργανόγραμμα, χρήστες, θέσεις, managers και δικαιώματα.', 'category' => 'Core', 'included' => true, 'dependencies' => []],
        'SALES_PROGRAM' => ['title' => 'Πρόγραμμα Πωλητών', 'summary' => 'Ημερήσιο πρόγραμμα πωλητών και επισκέψεις πεδίου.', 'category' => 'Field', 'included' => true, 'dependencies' => []],
        'DISPATCH' => ['title' => 'Δρομολόγια', 'summary' => 'Περιοχές και προγραμματισμός ημερήσιων δρομολογίων.', 'category' => 'Operations', 'included' => true, 'dependencies' => []],
        'FLEET' => ['title' => 'Στόλος', 'summary' => 'Οχήματα και βασική διαχείριση στόλου.', 'category' => 'Operations', 'included' => false, 'dependencies' => ['DISPATCH']],
        'PROCESS_LIBRARY' => ['title' => 'SOPs / Διαδικασίες', 'summary' => 'Βιβλιοθήκη διαδικασιών και πολιτικών.', 'category' => 'Knowledge', 'included' => true, 'dependencies' => []],
        'ONBOARDING' => ['title' => 'Onboarding εργαζομένων', 'summary' => 'Πρότυπα και tasks ένταξης νέων εργαζομένων.', 'category' => 'People', 'included' => false, 'dependencies' => ['ORGANIZATION']],
    ],

    'navigation' => [
        ['key' => 'dashboard', 'label' => 'Αρχική', 'description' => 'Dashboard ημέρας', 'module' => null, 'roles' => 'all'],
        ['key' => 'my-requests', 'label' => 'Οι αιτήσεις μου', 'description' => 'Προσωπικά αιτήματα', 'module' => 'APPROVALS', 'roles' => 'all'],
        ['key' => 'leave-requests', 'label' => 'Άδειες', 'description' => 'Άδειες και ημερολόγιο', 'module' => 'LEAVES', 'roles' => 'all'],
        ['key' => 'announcements', 'label' => 'Ενημερώσεις', 'description' => 'Updates εταιρείας', 'module' => 'ANNOUNCEMENTS', 'roles' => 'all'],
        ['key' => 'meetings', 'label' => 'Meetings', 'description' => 'Συναντήσεις και follow-ups', 'module' => 'MEETINGS', 'roles' => 'all'],
        ['key' => 'process-library', 'label' => 'SOPs', 'description' => 'Διαδικασίες', 'module' => 'PROCESS_LIBRARY', 'roles' => 'all'],
        ['key' => 'sales-program', 'label' => 'Πωλητές', 'description' => 'Πρόγραμμα ημέρας', 'module' => 'SALES_PROGRAM', 'permission' => 'can_view_sales_program'],
        ['key' => 'organization', 'label' => 'Οργάνωση', 'description' => 'Άνθρωποι και δομή', 'module' => 'ORGANIZATION', 'permission' => 'can_view_people_information'],
        ['key' => 'dispatch', 'label' => 'Δρομολόγια', 'description' => 'Περιοχές και στόλος', 'module' => 'DISPATCH', 'permission' => 'can_access_dispatch_board'],
        ['key' => 'admin', 'label' => 'Διαχείριση', 'description' => 'Κανόνες και έλεγχος', 'module' => null, 'permission' => 'can_manage_organization'],
    ],

    'dashboard_slots' => [
        'MEDIUM_PRIMARY' => ['title' => 'Μεσαίο αριστερά', 'size' => 'medium'],
        'MEDIUM_SECONDARY' => ['title' => 'Μεσαίο κέντρο', 'size' => 'medium'],
        'MEDIUM_TERTIARY' => ['title' => 'Μεσαίο δεξιά', 'size' => 'medium'],
        'SMALL' => ['title' => 'Μικρό', 'size' => 'small'],
        'LARGE' => ['title' => 'Μεγάλο', 'size' => 'large'],
        'MANAGER_PRIMARY' => ['title' => 'Διοικητικό αριστερά', 'size' => 'medium', 'manager_only' => true],
        'MANAGER_SECONDARY' => ['title' => 'Διοικητικό δεξιά', 'size' => 'medium', 'manager_only' => true],
    ],

    'widgets' => [
        'ACTION_INBOX' => ['title' => 'Άμεσες ενέργειες', 'summary' => 'Ό,τι χρειάζεται προσοχή από τον χρήστη ή την ομάδα του.', 'module' => null, 'viewer_group' => 'both', 'allowed_roles' => 'all', 'default_slot' => 'MEDIUM_PRIMARY'],
        'MY_REQUESTS' => ['title' => 'Οι αιτήσεις μου', 'summary' => 'Προσωπικά ανοιχτά αιτήματα και πρόσφατη κατάσταση.', 'module' => 'APPROVALS', 'viewer_group' => 'both', 'allowed_roles' => 'all', 'default_slot' => 'MEDIUM_TERTIARY'],
        'TEAM_TODAY' => ['title' => 'Η ομάδα μου σήμερα', 'summary' => 'Σύνοψη ομάδας με διαθέσιμους, άδειες και κατάσταση πεδίου.', 'module' => 'ORGANIZATION', 'viewer_group' => 'manager', 'allowed_roles' => ['SUPERVISOR', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'MANAGER_PRIMARY'],
        'MEETINGS_UPCOMING' => ['title' => 'Επερχόμενα meetings', 'summary' => 'Οι επόμενες συναντήσεις και βασικά follow-ups.', 'module' => 'MEETINGS', 'viewer_group' => 'both', 'allowed_roles' => 'all', 'default_slot' => 'LARGE'],
        'LEAVES_TODAY' => ['title' => 'Ποιοι λείπουν σήμερα', 'summary' => 'Σημερινές εγκεκριμένες απουσίες.', 'module' => 'LEAVES', 'viewer_group' => 'manager', 'allowed_roles' => ['SUPERVISOR', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'MEDIUM_SECONDARY'],
        'LEAVES_PENDING_DECISIONS' => ['title' => 'Εκκρεμείς αποφάσεις αδειών', 'summary' => 'Άδειες που περιμένουν απόφαση από προϊστάμενο, HR ή Operations.', 'module' => 'LEAVES', 'viewer_group' => 'manager', 'allowed_roles' => ['SUPERVISOR', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'MANAGER_PRIMARY'],
        'APPROVALS_RECENT_REQUESTS' => ['title' => 'Εκκρεμείς εγκρίσεις', 'summary' => 'Ζωντανή ουρά αιτημάτων που περιμένουν επόμενο βήμα έγκρισης.', 'module' => 'APPROVALS', 'viewer_group' => 'manager', 'allowed_roles' => ['SUPERVISOR', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'MEDIUM_TERTIARY'],
        'SALES_REP_STATUS' => ['title' => 'Πωλητές σήμερα', 'summary' => 'Ποιοι έχουν ξεκινήσει τη μέρα πεδίου και ποιοι όχι.', 'module' => 'SALES_PROGRAM', 'viewer_group' => 'manager', 'allowed_roles' => ['COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'SMALL'],
        'SALES_PROGRAM_SPOTLIGHT' => ['title' => 'Το σημερινό μου πλάνο', 'summary' => 'Στάσεις, ώρες και βασικές επισκέψεις του χρήστη.', 'module' => 'SALES_PROGRAM', 'viewer_group' => 'basic', 'allowed_roles' => ['SALES_REP', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'LARGE'],
        'DISPATCH_AREAS_TODAY' => ['title' => 'Περιοχές δρομολογίων σήμερα', 'summary' => 'Περιοχές που έχουν δρομολόγιο σήμερα.', 'module' => 'DISPATCH', 'viewer_group' => 'manager', 'allowed_roles' => ['STANDARD_USER', 'SUPERVISOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'MANAGER_SECONDARY'],
        'ANNOUNCEMENTS_PRIORITY' => ['title' => 'Σημαντικές ανακοινώσεις', 'summary' => 'Ενημερώσεις που δεν πρέπει να χαθούν.', 'module' => 'ANNOUNCEMENTS', 'viewer_group' => 'both', 'allowed_roles' => 'all', 'default_slot' => 'SMALL'],
        'ANNOUNCEMENTS_FEED' => ['title' => 'Ροή ενημερώσεων εταιρείας', 'summary' => 'Πρόσφατες ενημερώσεις σε μορφή feed.', 'module' => 'ANNOUNCEMENTS', 'viewer_group' => 'both', 'allowed_roles' => 'all', 'default_slot' => 'MANAGER_SECONDARY'],
        'MEETINGS_OPEN_ACTIONS' => ['title' => 'Ανοιχτές ενέργειες συναντήσεων', 'summary' => 'Follow-ups από meetings που θέλουν ιδιοκτήτη και κλείσιμο.', 'module' => 'MEETINGS', 'viewer_group' => 'manager', 'allowed_roles' => ['SUPERVISOR', 'COMMERCIAL_DIRECTOR', 'MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'LARGE'],
        'PROCESS_LIBRARY_UPDATES' => ['title' => 'Πρόσφατες αλλαγές διαδικασιών', 'summary' => 'Νέες ή ενημερωμένες διαδικασίες.', 'module' => 'PROCESS_LIBRARY', 'viewer_group' => 'both', 'allowed_roles' => 'all', 'default_slot' => 'MEDIUM_TERTIARY'],
        'ONBOARDING_PROGRESS' => ['title' => 'Πρόοδος νέων εργαζομένων', 'summary' => 'Πρόοδος onboarding flows και tasks ένταξης.', 'module' => 'ONBOARDING', 'viewer_group' => 'manager', 'allowed_roles' => ['MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'LARGE'],
        'ONBOARDING_BLOCKERS' => ['title' => 'Εμπόδια onboarding', 'summary' => 'Tasks που έχουν μπλοκάρει ή ξεπεράσει προθεσμία.', 'module' => 'ONBOARDING', 'viewer_group' => 'manager', 'allowed_roles' => ['MANAGEMENT', 'OPERATIONS_ADMIN', 'SYSTEM_ADMIN'], 'default_slot' => 'MANAGER_PRIMARY'],
    ],

    'default_widgets' => [
        'operations' => ['ACTION_INBOX', 'TEAM_TODAY', 'LEAVES_TODAY', 'LEAVES_PENDING_DECISIONS', 'APPROVALS_RECENT_REQUESTS', 'MY_REQUESTS', 'MEETINGS_UPCOMING', 'MEETINGS_OPEN_ACTIONS', 'DISPATCH_AREAS_TODAY', 'ANNOUNCEMENTS_PRIORITY', 'ANNOUNCEMENTS_FEED', 'PROCESS_LIBRARY_UPDATES', 'SALES_REP_STATUS'],
        'manager' => ['ACTION_INBOX', 'TEAM_TODAY', 'APPROVALS_RECENT_REQUESTS', 'MY_REQUESTS', 'LEAVES_TODAY', 'LEAVES_PENDING_DECISIONS', 'MEETINGS_UPCOMING', 'MEETINGS_OPEN_ACTIONS', 'ANNOUNCEMENTS_PRIORITY', 'ANNOUNCEMENTS_FEED', 'PROCESS_LIBRARY_UPDATES', 'SALES_REP_STATUS'],
        'sales' => ['SALES_PROGRAM_SPOTLIGHT', 'MY_REQUESTS', 'ACTION_INBOX', 'MEETINGS_UPCOMING', 'ANNOUNCEMENTS_PRIORITY', 'ANNOUNCEMENTS_FEED', 'PROCESS_LIBRARY_UPDATES'],
        'basic' => ['ACTION_INBOX', 'MY_REQUESTS', 'MEETINGS_UPCOMING', 'ANNOUNCEMENTS_PRIORITY', 'ANNOUNCEMENTS_FEED', 'PROCESS_LIBRARY_UPDATES'],
    ],
];
