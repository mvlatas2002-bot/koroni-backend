<!doctype html>
<html lang="el">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Koroni Portal' }}</title>
    <style>
        :root {
            --navy: #142237;
            --navy-soft: #1d304a;
            --ink: #162033;
            --muted: #66758e;
            --soft: #eef3f8;
            --panel: #ffffff;
            --line: #dce5f0;
            --ice: #f7fafc;
            --red: #d81b4c;
            --amber: #b85b0b;
            --green: #087f5b;
            --shadow: 0 22px 65px rgba(22, 32, 51, .08);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: "Segoe UI", "Aptos", Calibri, Arial, sans-serif;
            background: var(--soft);
            color: var(--ink);
        }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }
        h1, h2, h3, p { margin: 0; }
        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 26px;
            padding: 20px;
        }
        .shell > aside.sidebar + aside.sidebar { display: none; }
        .sidebar {
            position: sticky;
            top: 20px;
            height: calc(100vh - 40px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border-radius: 34px;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.12), transparent 28%),
                linear-gradient(180deg, #17263d 0%, #101b2d 100%);
            color: white;
            padding: 24px;
            box-shadow: var(--shadow);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 22px;
            border-bottom: 1px solid rgba(255,255,255,.10);
        }
        .brand-mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: rgba(255,255,255,.12);
            font-weight: 900;
            letter-spacing: -.05em;
        }
        .brand strong { display: block; font-size: 18px; letter-spacing: .02em; }
        .brand span { display: block; margin-top: 3px; color: rgba(255,255,255,.55); font-size: 12px; font-weight: 700; }
        .nav { margin-top: 22px; display: grid; gap: 8px; overflow: auto; padding-right: 4px; }
        .nav-section { display: grid; gap: 8px; }
        .nav-link {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            align-items: center;
            gap: 12px;
            border-radius: 22px;
            padding: 11px 12px;
            color: rgba(255,255,255,.78);
            transition: .18s ease;
        }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,.11); color: white; }
        .nav-link.active { box-shadow: inset 0 0 0 1px rgba(255,255,255,.08); }
        .nav-link-home {
            grid-template-columns: 44px minmax(0, 1fr);
            min-height: 66px;
            align-items: center;
        }
        .nav-link-home.active {
            background: rgba(255,255,255,.16);
            box-shadow:
                inset 0 0 0 1px rgba(255,255,255,.14),
                0 12px 26px rgba(0,0,0,.10);
        }
        .nav-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background: rgba(255,255,255,.08);
            font-weight: 900;
        }
        .nav-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .nav-link strong { display: block; font-size: 14px; }
        .nav-link > span:not(.nav-icon) { display: block; margin-top: 0; color: inherit; font-size: inherit; font-weight: inherit; }
        .nav-link > span:not(.nav-icon) > span { display: block; margin-top: 2px; color: rgba(255,255,255,.48); font-size: 11px; font-weight: 700; }
        .nav-link-home .nav-icon {
            width: 42px;
            height: 42px;
            display: inline-grid;
            place-items: center;
            border-radius: 16px;
            background: rgba(255,255,255,.16);
            color: #fff;
            padding: 0;
            line-height: 0;
            margin-top: 0;
        }
        .nav-link-home .nav-icon svg {
            display: block;
            width: 21px;
            height: 21px;
            stroke-width: 2.5;
            transform: none;
        }
        .nav-link-home strong {
            font-size: 15px;
            font-weight: 950;
            color: #fff;
        }
        .nav-group {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
        }
        .nav-group summary {
            list-style: none;
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) 18px;
            align-items: center;
            gap: 12px;
            border-radius: 22px;
            padding: 11px 12px;
            color: rgba(255,255,255,.78);
            cursor: pointer;
            position: relative;
            transition: background .18s ease, color .18s ease, box-shadow .18s ease;
        }
        .nav-group summary::-webkit-details-marker { display: none; }
        .nav-group summary:hover {
            background: rgba(255,255,255,.11);
            color: white;
        }
        .nav-group[open] summary,
        .nav-group.active summary {
            background:
                linear-gradient(90deg, rgba(255,255,255,.14), rgba(255,255,255,.07));
            color: white;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.08);
        }
        .nav-group.active summary::before {
            content: "";
            position: absolute;
            left: 0;
            top: 13px;
            bottom: 13px;
            width: 3px;
            border-radius: 999px;
            background: #f8fafc;
            box-shadow: 0 0 18px rgba(255,255,255,.38);
        }
        .nav-group.active summary .nav-icon,
        .nav-group[open] summary .nav-icon {
            background: rgba(255,255,255,.13);
            color: white;
        }
        .nav-group-title strong { display: block; font-size: 14px; }
        .nav-group-title span {
            display: block;
            margin-top: 2px;
            color: rgba(255,255,255,.48);
            font-size: 11px;
            font-weight: 700;
        }
        .nav-chevron {
            color: rgba(255,255,255,.45);
            font-size: 13px;
            font-weight: 900;
            transition: transform .18s ease;
        }
        .nav-group[open] .nav-chevron { transform: rotate(180deg); }
        .nav-submenu {
            margin: 7px 8px 4px 54px;
            display: grid;
            gap: 3px;
            border-left: 1px solid rgba(255,255,255,.12);
            padding-left: 12px;
        }
        .nav-sublink {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-radius: 14px;
            padding: 8px 10px;
            color: rgba(255,255,255,.58);
            font-size: 13px;
            font-weight: 800;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }
        .nav-sublink:hover {
            background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.92);
            transform: translateX(2px);
        }
        .nav-sublink.active {
            background: rgba(255,255,255,.13);
            color: white;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.08);
        }
        .nav-sublink.active::before {
            content: "";
            position: absolute;
            left: -14px;
            top: 50%;
            width: 5px;
            height: 5px;
            border-radius: 999px;
            background: white;
            box-shadow: 0 0 14px rgba(255,255,255,.75);
            transform: translateY(-50%);
        }
        .nav-sublink small {
            color: inherit;
            opacity: .55;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .sidebar-user {
            margin-top: auto;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 26px;
            background: rgba(255,255,255,.07);
            padding: 16px;
        }
        .sidebar-user-row { display: flex; gap: 12px; align-items: center; }
        .avatar {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: white;
            color: var(--navy);
            font-size: 13px;
            font-weight: 900;
        }
        .sidebar-user strong, .sidebar-user span { display: block; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sidebar-user strong { font-size: 14px; }
        .sidebar-user span { margin-top: 3px; color: rgba(255,255,255,.55); font-size: 12px; font-weight: 700; }
        .account-dock {
            position: fixed;
            z-index: 50;
            top: 20px;
            right: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .account-action-button,
        .account-menu summary {
            min-height: 48px;
            border: 1px solid rgba(213, 223, 235, .96);
            background: rgba(255,255,255,.96);
            box-shadow: 0 14px 34px rgba(22,32,51,.10);
        }
        .account-action-button {
            position: relative;
            width: 48px;
            display: grid;
            place-items: center;
            border-radius: 17px;
            color: var(--navy);
        }
        .account-action-button svg,
        .account-menu-link svg,
        .account-caret {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .account-action-button.has-badge > span {
            position: absolute;
            top: -8px;
            right: -7px;
            min-width: 25px;
            height: 22px;
            display: grid;
            place-items: center;
            border: 2px solid white;
            border-radius: 999px;
            background: #f0445f;
            color: white;
            font-size: 11px;
            font-weight: 950;
            line-height: 1;
        }
        .account-menu { position: relative; }
        .account-menu summary {
            display: grid;
            grid-template-columns: auto minmax(120px, 1fr) auto;
            align-items: center;
            gap: 10px;
            min-width: 208px;
            border-radius: 18px;
            padding: 7px 10px 7px 7px;
            color: var(--ink);
            list-style: none;
            cursor: pointer;
        }
        .account-menu summary::-webkit-details-marker { display: none; }
        .account-pill-avatar {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: linear-gradient(145deg, #17263d, #233a61);
            color: white;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .02em;
            line-height: 1;
            text-align: center;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.18);
        }
        .account-pill-avatar.large {
            width: 42px;
            height: 42px;
            border-radius: 15px;
            font-size: 12px;
        }
        .account-pill-copy {
            min-width: 0;
            display: grid;
            gap: 2px;
        }
        .account-pill-copy strong,
        .account-pill-copy span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .account-pill-copy strong { font-size: 13px; font-weight: 950; }
        .account-pill-copy span { color: var(--muted); font-size: 11px; font-weight: 800; }
        .account-caret {
            width: 16px;
            height: 16px;
            color: var(--muted);
        }
        .account-card {
            position: absolute;
            top: 60px;
            right: 0;
            width: min(304px, calc(100vw - 28px));
            border: 1px solid var(--line);
            border-radius: 26px;
            background: rgba(255,255,255,.98);
            box-shadow: 0 28px 70px rgba(22,32,51,.18);
            padding: 12px;
            color: var(--ink);
        }
        .account-card-hero {
            display: flex;
            align-items: center;
            gap: 11px;
            border: 1px solid #edf2f7;
            border-radius: 18px;
            background: #ffffff;
            padding: 10px;
            box-shadow: 0 8px 22px rgba(22,32,51,.04);
        }
        .account-card-hero > div strong,
        .account-card-hero > div span {
            display: block;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .account-card-hero > div strong { font-size: 13px; font-weight: 950; line-height: 1.15; }
        .account-card-hero > div span { margin-top: 3px; color: var(--muted); font-size: 11px; font-weight: 850; line-height: 1.15; }
        .account-card-hero .account-pill-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            line-height: 1;
        }
        .account-department {
            display: block;
            margin: 10px 0 10px;
            border-radius: 999px;
            background: #f8fbff;
            padding: 7px 10px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 900;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .account-menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            border: 0;
            border-top: 1px solid #eef3f8;
            background: transparent;
            color: var(--navy);
            cursor: pointer;
            font-weight: 900;
            padding: 14px 8px;
            text-align: left;
        }
        .account-menu-link:first-of-type { border-top: 0; }
        .account-menu-link.logout { color: var(--muted); }
        .account-menu-link:hover { color: var(--ink); }
        .account-menu form { margin: 0; }
        .account-menu[open] .account-caret { transform: rotate(180deg); }
        .account-menu[open] summary { border-color: #cbd8e8; }
        .account-menu-link svg { flex: 0 0 auto; color: #8da0ba; }
        .account-menu-link:hover svg { color: var(--navy); }
        .account-card .logout-button {
            border-color: var(--line);
            color: var(--ink);
        }
        .logout-button {
            width: 100%;
            margin-top: 14px;
            border: 1px solid rgba(255,255,255,.45);
            border-radius: 20px;
            background: transparent;
            color: white;
            cursor: pointer;
            font-weight: 900;
            padding: 12px;
        }
        .content { min-width: 0; padding: 8px 0 42px; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-bottom: 20px;
            padding-right: 430px;
        }
        .eyebrow {
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        h1 { margin-top: 7px; font-size: clamp(28px, 3vw, 42px); line-height: 1.04; letter-spacing: -.05em; }
        h2 { font-size: clamp(20px, 2vw, 30px); line-height: 1.1; letter-spacing: -.05em; }
        h3 { font-size: 17px; line-height: 1.2; letter-spacing: -.03em; }
        .muted { color: var(--muted); }
        .button {
            border: 1px solid var(--line);
            background: white;
            border-radius: 18px;
            color: var(--ink);
            cursor: pointer;
            font-weight: 900;
            padding: 11px 15px;
            box-shadow: 0 8px 22px rgba(22,32,51,.06);
        }
        .portal-grid { display: grid; gap: 18px; }
        .panel {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 34px;
            background: rgba(255,255,255,.94);
            box-shadow: var(--shadow);
            padding: 24px;
            overflow: hidden;
        }
        .surface {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 30px;
            background: rgba(255,255,255,.92);
            padding: 22px;
        }
        .surface.flush {
            border-color: transparent;
            box-shadow: none;
            background: transparent;
            padding: 0;
        }
        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 18px;
        }
        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fbfdff;
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            padding: 6px 12px;
            white-space: nowrap;
        }
        .pill.red { border-color: #ffc4d1; background: #fff1f4; color: var(--red); }
        .pill.green { border-color: #b7efd9; background: #effdf7; color: var(--green); }
        .pill.amber { border-color: #ffe0a8; background: #fffbeb; color: var(--amber); }
        .action-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .compact-list { display: grid; gap: 12px; }
        .list-item {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--ice);
            padding: 13px 14px;
        }
        .list-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .truncate { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .two-col { grid-template-columns: minmax(0, 1fr) minmax(340px, .62fr); align-items: start; }
        .three-col { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: stretch; }
        .two-even { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: stretch; }
        .five-col { grid-template-columns: 1.25fr 1fr 1fr 1.15fr auto; align-items: end; }
        .unit-submit .button { width: 100%; min-height: 52px; }
        .unit-list { margin-top: 20px; }
        .unit-rows { display: grid; gap: 12px; }
        .unit-row {
            display: grid;
            grid-template-columns: minmax(190px, 1.2fr) minmax(130px, .8fr) minmax(150px, .9fr) minmax(180px, 1.1fr) minmax(120px, .7fr) auto auto;
            gap: 12px;
            align-items: end;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: #fbfdff;
            padding: 14px;
        }
        .unit-row .field { margin-top: 0; }
        .unit-meta {
            display: grid;
            gap: 4px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
            padding-bottom: 10px;
        }
        .empty {
            min-height: 118px;
            display: grid;
            place-items: center;
            border: 1px dashed #e2e9f3;
            border-radius: 26px;
            background: #fbfcfe;
            text-align: center;
            color: var(--muted);
            padding: 18px;
        }
        .table-wrap { overflow: auto; border: 1px solid var(--line); border-radius: 22px; }
        table { width: 100%; border-collapse: collapse; min-width: 860px; background: white; }
        th, td { padding: 13px 15px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; }
        th { color: var(--muted); font-size: 11px; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
        td { font-size: 14px; }
        tr:last-child td { border-bottom: 0; }
        .org-tree { display: grid; gap: 14px; }
        .org-unit {
            border-left: 2px solid #dfe8f2;
            padding-left: 16px;
            display: grid;
            gap: 12px;
        }
        .org-unit.depth-0 {
            border-left: 0;
            padding-left: 0;
        }
        .org-unit-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: white;
            padding: 16px;
        }
        .org-unit-main h3 {
            margin-top: 6px;
            font-size: 21px;
        }
        .org-children {
            display: grid;
            gap: 14px;
            margin-left: 18px;
        }
        .org-people {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
        }
        .org-person {
            min-width: 0;
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fbfdff;
            padding: 12px;
        }
        .org-person.leader {
            background: linear-gradient(180deg, #ffffff, #f7fbff);
            border-color: #cfe0f2;
        }
        .organization-page {
            display: grid;
            gap: 14px;
        }
        .compact-topbar {
            margin-bottom: 2px;
        }
        .compact-topbar h1 {
            font-size: clamp(30px, 3vw, 46px);
        }
        .compact-topbar .muted {
            max-width: 820px;
            margin-top: 8px;
            font-size: 15px;
        }
        .org-company-card {
            min-width: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: linear-gradient(135deg, #17263d, #23395d);
            color: white;
            padding: 16px 20px;
            box-shadow: 0 18px 44px rgba(22, 32, 51, .10);
        }
        .org-company-card .eyebrow {
            color: #dce8f7;
        }
        .org-company-card h2 {
            margin-top: 3px;
            font-size: 22px;
        }
        .org-directorate-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            align-items: start;
        }
        .org-directorate-card {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 30px;
            background: rgba(255,255,255,.94);
            padding: 18px;
            box-shadow: 0 18px 42px rgba(22, 32, 51, .06);
        }
        .org-directorate-head,
        .org-unit-title,
        .org-details summary {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }
        .org-directorate-head h2 {
            margin-top: 4px;
            font-size: 25px;
        }
        .org-leader-strip {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-top: 12px;
            border: 1px solid #d9e3ee;
            border-radius: 20px;
            background: #f8fbff;
            padding: 11px 12px;
        }
        .org-leader-strip strong,
        .mini-person strong,
        .org-detail-person strong {
            display: block;
            color: var(--ink);
            font-size: 14px;
            line-height: 1.2;
        }
        .org-leader-strip span,
        .mini-person span,
        .org-detail-person span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.25;
            margin-top: 3px;
        }
        .avatar {
            display: inline-grid;
            place-items: center;
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
            border-radius: 14px;
            background: #17263d;
            color: white;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .02em;
        }
        .avatar.small {
            width: 31px;
            height: 31px;
            border-radius: 12px;
            background: #eaf0f7;
            color: #243652;
        }
        .org-unit-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .org-unit-card {
            min-width: 0;
            border: 1px solid #dbe4ee;
            border-radius: 22px;
            background: #fbfdff;
            padding: 12px;
        }
        .org-unit-card h3 {
            margin-top: 3px;
            font-size: 16px;
            line-height: 1.15;
        }
        .mini-person {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 10px;
            border-radius: 16px;
            background: white;
            padding: 9px;
        }
        .mini-person.leader {
            border: 1px solid #dce6f1;
        }
        .small-copy {
            margin-top: 10px;
            font-size: 12px;
        }
        .org-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 9px;
        }
        .org-chip-row span {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border: 1px solid #e1e8f1;
            border-radius: 999px;
            background: white;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            padding: 5px 8px;
        }
        .org-subunit-list {
            display: grid;
            gap: 7px;
            margin-top: 10px;
        }
        .org-details {
            border: 1px solid #e0e8f1;
            border-radius: 16px;
            background: white;
            padding: 8px 10px;
            margin-top: 9px;
        }
        .org-details summary {
            cursor: pointer;
            font-size: 12px;
            font-weight: 950;
            color: var(--ink);
            list-style: none;
        }
        .org-details summary::-webkit-details-marker {
            display: none;
        }
        .org-details summary em {
            flex: 0 0 auto;
            color: var(--muted);
            font-style: normal;
            font-size: 11px;
        }
        .org-detail-list {
            display: grid;
            gap: 6px;
            margin-top: 8px;
        }
        .org-detail-person {
            min-width: 0;
            border-radius: 13px;
            background: #f8fbff;
            padding: 7px 8px;
        }
        .org-detail-person.pinned {
            border: 1px solid #dbe6f2;
            background: #fff;
        }
        .corporate-org {
            display: grid;
            gap: 16px;
        }
        .org-node {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: rgba(255, 255, 255, .96);
            padding: 14px;
            box-shadow: 0 14px 34px rgba(22, 32, 51, .05);
        }
        .org-node.company {
            width: min(420px, 100%);
            justify-self: center;
            text-align: center;
            border-color: #a8b9d0;
            background: linear-gradient(180deg, #ffffff, #f7fbff);
        }
        .org-node.company h2 {
            margin-top: 4px;
            color: var(--navy);
            font-size: 22px;
            letter-spacing: -.02em;
        }
        .org-branch-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            align-items: start;
        }
        .org-branch {
            min-width: 0;
            display: grid;
            gap: 12px;
            border-radius: 30px;
            background: rgba(255, 255, 255, .44);
            padding: 12px;
            box-shadow: inset 0 0 0 1px rgba(220, 229, 240, .75);
        }
        .org-node.directorate {
            background: linear-gradient(145deg, #17263d, #23395d);
            color: white;
            border-color: rgba(255, 255, 255, .18);
        }
        .org-node.directorate .eyebrow,
        .org-node.directorate .org-manager small {
            color: rgba(255, 255, 255, .62);
        }
        .org-node.directorate h2 {
            margin-top: 5px;
            font-size: 24px;
        }
        .org-children-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            align-items: start;
        }
        .org-node.department h3 {
            margin-top: 4px;
            font-size: 16px;
        }
        .org-manager {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .12);
            padding: 9px;
        }
        .org-manager.compact {
            background: #f7fbff;
            border: 1px solid #dbe6f2;
            padding: 8px;
        }
        .org-manager small {
            display: block;
            color: var(--muted);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .org-manager strong {
            display: block;
            margin-top: 2px;
            font-size: 13px;
            line-height: 1.2;
        }
        .org-members {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 9px;
        }
        .org-members span {
            color: var(--muted);
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .org-members em {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border: 1px solid #e0e8f1;
            border-radius: 999px;
            background: white;
            color: var(--ink);
            font-size: 11px;
            font-style: normal;
            font-weight: 800;
            padding: 4px 7px;
        }
        .org-subtree {
            display: grid;
            gap: 7px;
            margin-top: 10px;
        }
        .org-subtree.visible {
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 8px;
            align-items: start;
        }
        .org-subtree.nested {
            margin-top: 8px;
            padding-left: 8px;
            border-left: 1px solid #dfe8f2;
        }
        .org-node.department.depth-1,
        .org-node.department.depth-2 {
            border-radius: 17px;
            padding: 10px;
            box-shadow: none;
            background: #fbfdff;
        }
        .org-node.department.depth-1 h3,
        .org-node.department.depth-2 h3 {
            font-size: 13px;
            letter-spacing: -.02em;
        }
        .org-node.department.depth-1 .eyebrow,
        .org-node.department.depth-2 .eyebrow {
            font-size: 10px;
            letter-spacing: .12em;
        }
        .org-node.department.depth-1 .org-manager,
        .org-node.department.depth-2 .org-manager {
            margin-top: 7px;
        }
        .org-node.department.depth-1 .org-members,
        .org-node.department.depth-2 .org-members {
            margin-top: 7px;
        }
        .org-node.department.depth-1 .org-members em,
        .org-node.department.depth-2 .org-members em {
            font-size: 10px;
            padding: 3px 6px;
        }
        .org-node.department.depth-1 .org-details,
        .org-node.department.depth-2 .org-details {
            padding: 6px 8px;
            border-radius: 13px;
        }
        .org-map {
            display: grid;
            gap: 18px;
        }
        .org-company-banner {
            width: min(520px, 100%);
            justify-self: center;
            text-align: center;
            border: 1px solid #c9d7e8;
            border-radius: 28px;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, .9), transparent 42%),
                linear-gradient(145deg, #ffffff, #f4f8fc);
            padding: 18px 24px;
            box-shadow: 0 18px 44px rgba(22, 32, 51, .07);
        }
        .org-company-banner h2 {
            margin-top: 4px;
            color: var(--navy);
            font-size: 26px;
            letter-spacing: -.03em;
        }
        .org-company-banner p {
            margin-top: 4px;
            color: var(--muted);
            font-weight: 800;
        }
        .org-directorate-map {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            align-items: start;
        }
        .org-directorate-panel {
            min-width: 0;
            display: grid;
            gap: 14px;
            border: 1px solid rgba(205, 216, 230, .85);
            border-radius: 34px;
            background: rgba(255, 255, 255, .58);
            padding: 12px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72), 0 18px 54px rgba(20, 34, 55, .06);
        }
        .org-directorate-hero {
            min-width: 0;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: start;
            border-radius: 26px;
            background:
                linear-gradient(135deg, rgba(255,255,255,.08), transparent 38%),
                linear-gradient(145deg, #17263d, #223a60);
            color: #fff;
            padding: 18px;
        }
        .org-directorate-hero .eyebrow,
        .org-directorate-hero small {
            color: rgba(255, 255, 255, .66);
        }
        .org-directorate-hero h2 {
            margin-top: 5px;
            font-size: clamp(22px, 2vw, 30px);
            letter-spacing: -.035em;
        }
        .org-directorate-hero > strong {
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            padding: 8px 12px;
            font-size: 13px;
        }
        .org-director-strip {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 22px;
            background: rgba(255, 255, 255, .12);
            padding: 12px;
        }
        .org-director-strip b {
            display: block;
            margin-top: 2px;
            font-size: 15px;
        }
        .org-department-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 12px;
            align-items: start;
        }
        .org-map-card {
            min-width: 0;
            border: 1px solid #d9e4f0;
            border-radius: 24px;
            background: rgba(255, 255, 255, .96);
            padding: 14px;
            box-shadow: 0 14px 34px rgba(22, 32, 51, .05);
        }
        .org-map-card.depth-1,
        .org-map-card.depth-2 {
            border-radius: 20px;
            background: #fbfdff;
            box-shadow: none;
        }
        .org-map-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .org-map-card-head h3 {
            margin-top: 4px;
            font-size: 18px;
            line-height: 1.12;
            letter-spacing: -.03em;
        }
        .org-map-card.depth-1 .org-map-card-head h3,
        .org-map-card.depth-2 .org-map-card-head h3 {
            font-size: 15px;
        }
        .org-map-card-head > strong {
            flex: 0 0 auto;
            border: 1px solid #dce6f1;
            border-radius: 999px;
            background: #f7fbff;
            color: #51637d;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 950;
        }
        .org-leader-card {
            display: grid;
            gap: 8px;
            margin-top: 12px;
        }
        .org-leader-card > span,
        .org-members-preview > span {
            color: var(--muted);
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .org-person-row {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #dde7f2;
            border-radius: 18px;
            background: linear-gradient(180deg, #fff, #f8fbff);
            padding: 10px;
        }
        .org-person-row strong {
            display: block;
            color: var(--ink);
            font-size: 14px;
            line-height: 1.15;
        }
        .org-person-row small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
        }
        .avatar.mini {
            width: 32px;
            height: 32px;
            border-radius: 12px;
            background: #eaf0f7;
            color: #20314d;
            font-size: 11px;
        }
        .org-members-preview {
            display: grid;
            gap: 8px;
            margin-top: 12px;
        }
        .org-members-preview div {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }
        .org-name-chip {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border: 1px solid #e0e8f1;
            border-radius: 999px;
            background: #fff;
            color: var(--ink);
            padding: 6px 9px;
            font-size: 12px;
            font-weight: 850;
        }
        .org-roster {
            margin-top: 12px;
            border: 1px solid #dce6f1;
            border-radius: 18px;
            background: #fff;
            padding: 9px 10px;
        }
        .org-roster summary {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            list-style: none;
            cursor: pointer;
            color: var(--ink);
            font-size: 13px;
            font-weight: 950;
        }
        .org-roster summary::-webkit-details-marker {
            display: none;
        }
        .org-roster summary em {
            color: var(--muted);
            font-style: normal;
            font-size: 12px;
        }
        .org-roster-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
            max-height: 360px;
            overflow: auto;
            padding-top: 10px;
        }
        .org-child-lane {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 10px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2eaf3;
        }
        .org-map-card.depth-1 .org-child-lane {
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        }
        .org-empty-note {
            margin-top: 12px;
            border: 1px dashed #dce6f1;
            border-radius: 16px;
            color: var(--muted);
            background: #fbfdff;
            padding: 12px;
            font-size: 13px;
            font-weight: 800;
        }
        details summary { cursor: pointer; font-weight: 900; }
        .payload {
            margin-top: 14px;
            max-height: 360px;
            overflow: auto;
            border-radius: 22px;
            background: #101827;
            color: #d8f7e8;
            padding: 16px;
            font-size: 12px;
            line-height: 1.55;
            white-space: pre-wrap;
        }
        .login-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at 15% 15%, rgba(20,34,55,.14), transparent 28%),
                linear-gradient(135deg, #eef3f8, #fbfaf7);
        }
        .login-card {
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) 420px;
            gap: 18px;
        }
        .login-hero {
            border-radius: 34px;
            background: linear-gradient(160deg, #17263d, #101b2d);
            color: white;
            padding: 34px;
            min-height: 520px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: var(--shadow);
        }
        .login-form {
            border: 1px solid var(--line);
            border-radius: 34px;
            background: white;
            padding: 30px;
            box-shadow: var(--shadow);
        }
        .field { display: grid; gap: 8px; margin-top: 18px; }
        .field label { font-weight: 900; }
        .field small {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: white;
            padding: 14px;
            color: var(--ink);
            outline: none;
        }
        .field select {
            min-height: 52px;
            cursor: pointer;
        }
        .field textarea {
            min-height: 118px;
            resize: vertical;
        }
        .field input:focus,
        .field select:focus,
        .field textarea:focus { border-color: #9fb2cc; box-shadow: 0 0 0 4px rgba(159,178,204,.18); }
        .user-form-shell {
            display: grid;
            gap: 20px;
            box-shadow: var(--shadow);
        }
        .user-form-shell .profile-section {
            margin: 0;
        }
        .profile-page .topbar {
            padding-right: 430px;
        }
        .profile-layout {
            display: grid;
            grid-template-columns: minmax(280px, .38fr) minmax(0, 1fr);
            gap: 18px;
            align-items: start;
        }
        .profile-identity {
            position: sticky;
            top: 24px;
            border: 1px solid var(--line);
            border-radius: 34px;
            background:
                radial-gradient(circle at 20% 0%, rgba(255,255,255,.78), transparent 34%),
                linear-gradient(160deg, #17263d, #20395c);
            color: white;
            box-shadow: var(--shadow);
            padding: 24px;
            overflow: hidden;
        }
        .profile-orb {
            width: 74px;
            height: 74px;
            display: grid;
            place-items: center;
            border-radius: 28px;
            background: rgba(255,255,255,.13);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.12);
            font-size: 20px;
            font-weight: 950;
            letter-spacing: .04em;
        }
        .profile-identity h2 {
            margin-top: 18px;
            font-size: 28px;
        }
        .profile-identity p {
            margin-top: 8px;
            color: rgba(255,255,255,.68);
            font-weight: 800;
        }
        .profile-facts {
            display: grid;
            gap: 10px;
            margin-top: 22px;
        }
        .profile-facts div {
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 20px;
            background: rgba(255,255,255,.07);
            padding: 12px;
        }
        .profile-facts span {
            display: block;
            color: rgba(255,255,255,.55);
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .profile-facts strong {
            display: block;
            margin-top: 5px;
            font-size: 14px;
            line-height: 1.25;
        }
        .profile-form {
            display: grid;
            gap: 16px;
        }
        .profile-section {
            border: 1px solid var(--line);
            border-radius: 32px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 18px 50px rgba(22,32,51,.06);
            padding: 22px;
        }
        .leave-calendar-page {
            display: grid;
            gap: 18px;
        }
        .leave-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            border: 1px solid #cfdbea;
            border-radius: 34px;
            background:
                radial-gradient(circle at 12% 10%, rgba(255,255,255,.95), transparent 34%),
                linear-gradient(135deg, #ffffff, #edf4fb);
            box-shadow: var(--shadow);
            padding: 24px;
        }
        .leave-hero h2 {
            margin-top: 8px;
            color: var(--navy);
        }
        .leave-balance-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(110px, 1fr));
            gap: 10px;
        }
        .leave-balance-strip span {
            border: 1px solid var(--line);
            border-radius: 20px;
            background: rgba(255,255,255,.82);
            color: var(--muted);
            font-size: 12px;
            font-weight: 850;
            padding: 12px;
        }
        .leave-balance-strip strong {
            display: block;
            color: var(--ink);
            font-size: 22px;
            line-height: 1.1;
        }
        .leave-calendar-surface,
        .leave-admin-surface {
            box-shadow: var(--shadow);
        }
        .calendar-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }
        .calendar-toolbar > div {
            text-align: center;
        }
        .leave-weekdays,
        .leave-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
        }
        .leave-weekdays {
            margin-bottom: 8px;
        }
        .leave-weekdays span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .12em;
            text-align: center;
            text-transform: uppercase;
        }
        .leave-day {
            min-height: 132px;
            border: 1px solid #dde7f2;
            border-radius: 18px;
            background: #fbfdff;
            padding: 10px;
            overflow: hidden;
        }
        .leave-day.today {
            border-color: #9fb2cc;
            box-shadow: inset 0 0 0 2px rgba(159,178,204,.24);
        }
        .leave-day.weekend,
        .leave-day.muted-day {
            background: #f4f7fb;
            color: #94a3b8;
        }
        .leave-day-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
            margin-bottom: 7px;
        }
        .leave-day-head strong {
            font-size: 15px;
            font-weight: 950;
        }
        .calendar-chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: white;
            color: var(--muted);
            font-size: 9px;
            font-weight: 950;
            letter-spacing: .06em;
            padding: 3px 6px;
            text-transform: uppercase;
        }
        .calendar-chip.holiday {
            border-color: #ffd6a8;
            background: #fff7ed;
            color: var(--amber);
        }
        .calendar-event {
            display: grid;
            gap: 1px;
            border-radius: 12px;
            margin-top: 5px;
            padding: 6px 7px;
            font-size: 11px;
            font-weight: 850;
            line-height: 1.18;
        }
        .calendar-event strong,
        .calendar-event span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .calendar-event span {
            color: var(--muted);
            font-size: 10px;
        }
        .calendar-event.leave.approved {
            border: 1px solid #b7efd9;
            background: #effdf7;
            color: var(--green);
        }
        .calendar-event.leave.pending {
            border: 1px solid #ffe0a8;
            background: #fffbeb;
            color: var(--amber);
        }
        .calendar-event.birthday {
            border: 1px solid #c8d7ff;
            background: #f1f5ff;
            color: #244478;
        }
        .calendar-event.holiday {
            border: 1px solid #ffd6a8;
            background: #fff7ed;
            color: var(--amber);
        }
        .calendar-more {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 900;
        }
        .leave-balance-table {
            display: grid;
            gap: 10px;
        }
        .leave-balance-row {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) 120px 120px minmax(150px, .6fr) auto;
            align-items: end;
            gap: 10px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--ice);
            padding: 12px;
        }
        .leave-person strong,
        .leave-person span,
        .leave-balance-row label span,
        .leave-mini-metrics span,
        .leave-mini-metrics strong {
            display: block;
        }
        .leave-person strong {
            font-weight: 950;
        }
        .leave-person span,
        .leave-balance-row label span,
        .leave-mini-metrics span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 850;
        }
        .leave-balance-row input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: white;
            padding: 10px;
        }
        .leave-mini-metrics strong {
            margin-top: 4px;
            color: var(--ink);
            font-size: 13px;
            font-weight: 950;
        }
        .approval-leave .field:has(input[type="date"]) {
            display: none;
        }
        .leave-request-picker {
            border: 1px solid #d7e2ee;
            border-radius: 28px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            padding: 18px;
            box-shadow: 0 18px 42px rgba(22,32,51,.06);
        }
        .leave-picker-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin: 16px 0;
        }
        .leave-picker-summary span {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: white;
            padding: 12px;
        }
        .leave-picker-summary strong,
        .leave-picker-summary small {
            display: block;
        }
        .leave-picker-summary strong {
            color: var(--ink);
            font-size: 15px;
            font-weight: 950;
        }
        .leave-picker-summary small {
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 850;
        }
        .leave-range-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
        }
        .leave-range-day {
            min-height: 82px;
            border: 1px solid #dde7f2;
            border-radius: 16px;
            background: #fbfdff;
            color: var(--ink);
            cursor: pointer;
            padding: 9px;
            text-align: left;
            transition: .16s ease;
        }
        .leave-range-day:hover {
            border-color: #9fb2cc;
            transform: translateY(-1px);
        }
        .leave-range-day strong,
        .leave-range-day span {
            display: block;
        }
        .leave-range-day strong {
            font-size: 15px;
            font-weight: 950;
        }
        .leave-range-day span {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 850;
            line-height: 1.2;
        }
        .leave-range-day.muted-day {
            opacity: .45;
        }
        .leave-range-day[data-working="0"] {
            background: #f4f7fb;
        }
        .leave-range-day.is-selected {
            border-color: #17263d;
            background: #17263d;
            color: white;
        }
        .leave-range-day.is-selected span {
            color: rgba(255,255,255,.7);
        }
        .leave-range-day.is-in-range {
            border-color: #b9c8dc;
            background: #edf4fb;
        }
        .primary-action {
            justify-self: start;
            background: var(--navy);
            border-color: var(--navy);
            color: white;
        }
        .notice-success {
            margin-bottom: 18px;
            border-color: #b7efd9;
            color: var(--green);
            font-weight: 900;
        }
        .error {
            margin-top: 16px;
            border: 1px solid #fecdd3;
            background: #fff1f2;
            color: #be123c;
            border-radius: 18px;
            padding: 12px;
            font-weight: 800;
        }
        @media (max-width: 1100px) {
            .shell { grid-template-columns: 1fr; padding: 12px; }
            .sidebar { position: static; height: auto; }
            .nav { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .three-col, .two-col, .two-even, .five-col, .unit-row, .login-card { grid-template-columns: 1fr; }
            .unit-row { align-items: stretch; }
            .profile-layout { grid-template-columns: 1fr; }
            .profile-identity { position: static; }
            .topbar,
            .profile-page .topbar { padding-right: 0; padding-top: 72px; }
            .account-dock { left: 14px; right: 14px; justify-content: flex-end; }
            .org-directorate-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .org-unit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .org-branch-row { grid-template-columns: 1fr; }
            .org-directorate-map { grid-template-columns: 1fr; }
        }
        @media (max-width: 520px) {
            .org-directorate-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .topbar { align-items: flex-start; flex-direction: column; }
            .topbar { padding-right: 0; padding-top: 122px; }
            .account-dock { top: 14px; right: 14px; }
            .account-dock { left: 14px; flex-wrap: wrap; justify-content: flex-end; }
            .account-menu { width: 100%; }
            .account-menu summary { grid-template-columns: auto minmax(0, 1fr) auto; min-width: 0; }
            .account-action-button { width: 44px; min-height: 44px; border-radius: 15px; }
            .account-card { top: 50px; }
            .panel { border-radius: 26px; padding: 18px; }
            .nav { grid-template-columns: 1fr; }
            .login-hero { min-height: auto; }
            .organization-page { gap: 10px; }
            .organization-page .topbar { gap: 10px; }
            .organization-page .topbar .muted { display: none; }
            .leave-hero,
            .calendar-toolbar { align-items: stretch; flex-direction: column; }
            .leave-balance-strip { grid-template-columns: 1fr; }
            .leave-picker-summary { grid-template-columns: 1fr; }
            .leave-weekdays { display: none; }
            .leave-calendar-grid { grid-template-columns: 1fr; }
            .leave-range-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .leave-day { min-height: auto; }
            .leave-balance-row { grid-template-columns: 1fr; }
            .org-company-card { padding: 12px 14px; border-radius: 20px; }
            .org-company-card h2 { font-size: 18px; }
            .org-directorate-grid { gap: 10px; }
            .org-directorate-card { padding: 10px; border-radius: 22px; }
            .org-branch { padding: 8px; border-radius: 22px; }
            .org-children-row { grid-template-columns: 1fr; }
            .org-node { padding: 10px; border-radius: 18px; }
            .org-node.directorate h2 { font-size: 18px; }
            .org-company-banner { padding: 14px 16px; border-radius: 22px; }
            .org-company-banner h2 { font-size: 20px; }
            .org-directorate-panel { padding: 8px; border-radius: 24px; }
            .org-directorate-hero { padding: 14px; border-radius: 20px; }
            .org-directorate-hero h2 { font-size: 20px; }
            .org-department-grid,
            .org-child-lane,
            .org-map-card.depth-1 .org-child-lane { grid-template-columns: 1fr; }
            .org-map-card { padding: 12px; border-radius: 20px; }
            .org-map-card-head h3 { font-size: 16px; }
            .org-roster-grid { grid-template-columns: 1fr; max-height: 300px; }
            .org-directorate-head h2 { font-size: 18px; }
            .org-leader-strip { margin-top: 8px; padding: 7px; border-radius: 15px; }
            .org-unit-card { padding: 8px; border-radius: 16px; }
            .org-unit-card h3 { font-size: 13px; }
            .org-unit-title .pill { min-height: 25px; padding: 3px 8px; font-size: 10px; }
            .mini-person { margin-top: 6px; padding: 6px; border-radius: 13px; }
            .mini-person strong,
            .org-detail-person strong { font-size: 12px; }
            .mini-person span,
            .org-detail-person span { font-size: 10px; }
            .avatar { width: 30px; height: 30px; border-radius: 11px; font-size: 10px; }
            .avatar.small { width: 24px; height: 24px; border-radius: 9px; }
            .org-chip-row { display: none; }
            .org-subunit-list { gap: 5px; margin-top: 7px; }
            .org-details { margin-top: 6px; padding: 6px 7px; border-radius: 12px; }
            .org-details summary { font-size: 10px; }
            .org-unit-grid { grid-template-columns: 1fr; }
            .org-company-card { align-items: flex-start; }
            .org-children { margin-left: 8px; }
            .org-person { grid-template-columns: 38px minmax(0, 1fr); }
            .org-person .pill { grid-column: 1 / -1; justify-self: start; }
        }
        .notifications-page .topbar { padding-right: 0; }
        .notification-settings {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 22px;
            align-items: start;
            margin-bottom: 18px;
        }
        .notification-settings-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .notification-settings-actions .primary-action,
        .notification-status { grid-column: 1 / -1; }
        .notification-status {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--ice);
            color: var(--muted);
            font-weight: 850;
            padding: 12px 14px;
        }
        .active-devices {
            grid-column: 1 / -1;
            border-top: 1px solid var(--line);
            padding-top: 14px;
            color: var(--muted);
            font-size: 14px;
        }
        .device-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .device-list span {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: white;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 800;
        }
        .notification-list {
            display: grid;
            gap: 10px;
        }
        .notification-row {
            display: grid;
            grid-template-columns: 12px minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fbfdff;
            padding: 14px;
        }
        .notification-row.unread {
            border-color: #bdd4f4;
            background: linear-gradient(135deg, #fff, #f3f8ff);
            box-shadow: 0 14px 34px rgba(30, 58, 95, .08);
        }
        .notification-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #9aa9bd;
        }
        .notification-row.unread .notification-dot { background: var(--red); }
        .notification-row strong,
        .notification-row small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .notification-row small {
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
        }
        .notification-row time {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        @media (max-width: 900px) {
            .notification-settings { grid-template-columns: 1fr; }
            .notification-settings-actions { grid-template-columns: 1fr; }
            .notification-row { grid-template-columns: 10px minmax(0, 1fr); }
            .notification-row time { grid-column: 2; }
        }
    </style>
    <script>
        document.addEventListener('toggle', (event) => {
            const current = event.target;

            if (!(current instanceof HTMLDetailsElement) || !current.open) {
                return;
            }

            const scope = current.closest('[data-accordion-scope]');

            if (!scope) {
                return;
            }

            scope.querySelectorAll('details[open]').forEach((details) => {
                if (details !== current && details.closest('[data-accordion-scope]') === scope) {
                    details.open = false;
                }
            });
        }, true);

        document.addEventListener('DOMContentLoaded', () => {
            const picker = document.querySelector('[data-leave-picker]');
            if (!picker) {
                return;
            }

            const form = picker.closest('form');
            const startInput = form?.querySelector('input[name="starts_on"]');
            const endInput = form?.querySelector('input[name="ends_on"]');
            const leaveTypeInput = form?.querySelector('[data-leave-type]');
            const days = [...picker.querySelectorAll('[data-date]')];
            const rangeLabel = picker.querySelector('[data-leave-range-label]');
            const daysLabel = picker.querySelector('[data-leave-days-label]');
            const daysCaption = picker.querySelector('[data-leave-days-caption]');
            let start = picker.dataset.initialStart || startInput?.value || null;
            let end = picker.dataset.initialEnd || endInput?.value || null;

            const formatDate = (value) => {
                if (!value) return '';
                const [year, month, day] = value.split('-');
                return `${day}/${month}/${year}`;
            };

            const normalizeRange = () => {
                if (start && end && start > end) {
                    [start, end] = [end, start];
                }
            };

            const update = () => {
                normalizeRange();

                if (startInput) startInput.value = start || '';
                if (endInput) endInput.value = end || start || '';

                let chargedDays = 0;
                days.forEach((button) => {
                    const date = button.dataset.date;
                    const selected = date === start || date === end;
                    const inRange = start && (end || start) && date >= start && date <= (end || start);

                    button.classList.toggle('is-selected', selected);
                    button.classList.toggle('is-in-range', Boolean(inRange && !selected));

                    if (inRange && button.dataset.working === '1') {
                        chargedDays++;
                    }
                });

                if (rangeLabel) {
                    rangeLabel.textContent = start
                        ? `${formatDate(start)}${end && end !== start ? ` - ${formatDate(end)}` : ''}`
                        : 'Δεν έχεις επιλέξει ημέρες';
                }

                if (daysLabel) {
                    daysLabel.textContent = leaveTypeInput?.value === 'Αναρρωτική άδεια' ? '0' : chargedDays;
                }

                if (daysCaption) {
                    daysCaption.textContent = leaveTypeInput?.value === 'Αναρρωτική άδεια'
                        ? 'Δεν αφαιρείται από το υπόλοιπο'
                        : 'Χρεώσιμες εργάσιμες';
                }
            };

            days.forEach((button) => {
                button.addEventListener('click', () => {
                    const date = button.dataset.date;

                    if (!start || (start && end)) {
                        start = date;
                        end = null;
                    } else {
                        end = date;
                    }

                    update();
                });
            });

            leaveTypeInput?.addEventListener('change', update);
            update();
        });

        document.addEventListener('DOMContentLoaded', () => {
            const panel = document.querySelector('[data-push-panel]');
            if (!panel) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const status = panel.querySelector('[data-push-status]');
            const enableButton = panel.querySelector('[data-push-enable]');
            const disableButton = panel.querySelector('[data-push-disable]');
            const testButton = panel.querySelector('[data-push-test]');
            const pushReady = panel.dataset.pushReady === '1';
            const vapidPublicKey = panel.dataset.vapidPublicKey || '';

            const setStatus = (message) => {
                if (status) status.textContent = message;
            };

            const urlBase64ToUint8Array = (base64String) => {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const rawData = window.atob(base64);
                return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
            };

            const postJson = async (url, payload = {}) => {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json();
            };

            const currentSubscription = async () => {
                const registration = await navigator.serviceWorker.register('/sw.js');
                return {
                    registration,
                    subscription: await registration.pushManager.getSubscription(),
                };
            };

            const refreshButtons = (enabled) => {
                if (enableButton) enableButton.disabled = !pushReady || enabled;
                if (disableButton) disableButton.disabled = !enabled;
                if (testButton) testButton.disabled = !enabled;
            };

            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                setStatus('Ο browser δεν υποστηρίζει push ειδοποιήσεις.');
                refreshButtons(false);
                return;
            }

            if (!pushReady || !vapidPublicKey) {
                refreshButtons(false);
                return;
            }

            enableButton?.addEventListener('click', async () => {
                try {
                    setStatus('Ζητάμε άδεια από τον browser...');
                    const permission = await Notification.requestPermission();

                    if (permission !== 'granted') {
                        setStatus('Δεν δόθηκε άδεια για push ειδοποιήσεις.');
                        return;
                    }

                    const { registration } = await currentSubscription();
                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                    });

                    await postJson('/push/subscribe', subscription.toJSON());
                    setStatus('Push ενεργό για αυτή τη συσκευή.');
                    refreshButtons(true);
                } catch (error) {
                    setStatus(`Η ενεργοποίηση απέτυχε: ${error.message}`);
                }
            });

            disableButton?.addEventListener('click', async () => {
                try {
                    const { subscription } = await currentSubscription();
                    const endpoint = subscription?.endpoint || null;
                    await subscription?.unsubscribe().catch(() => undefined);
                    await postJson('/push/unsubscribe', { endpoint });
                    setStatus('Push ανενεργό για αυτή τη συσκευή.');
                    refreshButtons(false);
                } catch (error) {
                    setStatus(`Η απενεργοποίηση απέτυχε: ${error.message}`);
                }
            });

            testButton?.addEventListener('click', async () => {
                try {
                    setStatus('Στέλνουμε δοκιμαστική ειδοποίηση...');
                    await postJson('/push/test');
                    setStatus('Η δοκιμή στάλθηκε. Αν η συσκευή επιτρέπει ειδοποιήσεις, θα εμφανιστεί push.');
                } catch (error) {
                    setStatus(`Η δοκιμή απέτυχε: ${error.message}`);
                }
            });
        });
    </script>
</head>
<body>
    @yield('body')
</body>
</html>
