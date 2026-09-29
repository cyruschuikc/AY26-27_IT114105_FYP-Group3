<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TeamBox | Instructor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f0f1f3;
            color: #2c2f36;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 210px;
            min-height: 100vh;
            background: #16181d;
            color: #fff;
            padding: 26px 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            align-self: flex-start;
        }

        .sidebar .brand {
            padding: 0 22px 26px;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.3px;
            color: #fff;
        }

        .sidebar a {
            display: block;
            padding: 10px 22px;
            color: #8b8f98;
            font-size: 13.5px;
            text-decoration: none;
            cursor: pointer;
            border-left: 2px solid transparent;
            transition: color 0.15s;
        }

        .sidebar a:hover {
            color: #dfe1e5;
        }

        .sidebar a.active {
            color: #fff;
            font-weight: 500;
            border-left-color: #fff;
        }

        .sidebar a.logout {
            color: #6b6f78;
            margin-top: auto;
        }

        .sidebar a.logout:hover {
            color: #a8acb4;
        }

        /* Main */
        .main {
            flex: 1;
            padding: 38px 44px;
            max-width: calc(100% - 210px);
        }

        h1 {
            font-size: 21px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 13px;
            color: #8b8f98;
            margin-bottom: 38px;
        }

        h2 {
            font-size: 13px;
            font-weight: 600;
            color: #6b6f78;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 0 0 14px;
        }

        /* Card */
        .card {
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            padding: 6px 20px;
            margin-bottom: 30px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            text-align: left;
            font-weight: 500;
            font-size: 12px;
            color: #8b8f98;
            padding: 12px 10px 12px 0;
            border-bottom: 1px solid #eceef1;
        }

        td {
            padding: 14px 10px 14px 0;
            border-bottom: 1px solid #f2f3f5;
            color: #2c2f36;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .muted {
            color: #a8acb4;
            font-size: 13px;
        }

        /* Pill - light gray */
        .pill {
            display: inline-block;
            font-size: 12px;
            padding: 3px 10px;
            border-radius: 20px;
            background: #f2f3f5;
            color: #6b6f78;
            font-weight: 500;
        }

        /* List */
        ul {
            list-style: none;
            font-size: 14px;
        }

        ul li {
            padding: 13px 0;
            border-bottom: 1px solid #f2f3f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        ul li:last-child {
            border-bottom: none;
        }

        /* Text links */
        .link {
            background: none;
            border: none;
            color: #6b6f78;
            font-size: 13px;
            cursor: pointer;
            padding: 0;
        }

        .link:hover {
            color: #2c2f36;
            text-decoration: underline;
        }

        @media (max-width: 700px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; position: static; }
            .main { max-width: 100%; padding: 25px 22px; }
            .card { padding: 6px 14px; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">TeamBox</div>
    <a class="active">Dashboard</a>
    <a>Projects</a>
    <a>Teams</a>
    <a>Students</a>
    <a>Contribution Reports</a>
    <a>Files</a>
    <a>Announcements</a>
    <a>Profile</a>
    <a class="logout">Log Out</a>
</aside>

<main class="main">

    <h1>Instructor Dashboard</h1>
    <p class="subtitle">ITE4116M Final Year Project · Esther Yuen · 2026/27</p>

    <h2>Teams</h2>
    <div class="card">
        <table>
            <tr>
                <th>Team</th>
                <th>Leader</th>
                <th>Members</th>
                <th>Progress</th>
                <th>Status</th>
                <th></th>
            </tr>
            <tr>
                <td>Team 01</td>
                <td>CHAN Ho Yin</td>
                <td>4 / 4</td>
                <td>3 / 5</td>
                <td><span class="pill">Executing</span></td>
                <td><button class="link">View</button></td>
            </tr>
            <tr>
                <td>Team 02</td>
                <td>LAI Ka-Yu Savio</td>
                <td>4 / 4</td>
                <td>1 / 5</td>
                <td><span class="pill">Voting</span></td>
                <td><button class="link">View</button></td>
            </tr>
            <tr>
                <td>Team 03</td>
                <td>MIAO Tung</td>
                <td>3 / 4</td>
                <td>0 / 5</td>
                <td><span class="pill">Recruiting</span></td>
                <td><button class="link">View</button></td>
            </tr>
            <tr>
                <td>Team 04</td>
                <td class="muted">—</td>
                <td>2 / 4</td>
                <td class="muted">—</td>
                <td><span class="pill">Recruiting</span></td>
                <td><button class="link">View</button></td>
            </tr>
        </table>
    </div>

    <h2>Unassigned Students</h2>
    <div class="card">
        <ul>
            <li>
                <span>WONG Mei Ling <span class="muted">202611234 · Year 2</span></span>
                <button class="link">Assign</button>
            </li>
            <li>
                <span>LEE Chun Kit <span class="muted">202645678 · Year 1</span></span>
                <button class="link">Assign</button>
            </li>
            <li>
                <span>CHEUNG Ka Wai <span class="muted">202678901 · Year 3</span></span>
                <button class="link">Assign</button>
            </li>
            <li>
                <span>NG Wing Yan <span class="muted">202632145 · Year 2</span></span>
                <button class="link">Assign</button>
            </li>
            <li>
                <span>HO Pui Shan <span class="muted">202654321 · Year 1</span></span>
                <button class="link">Assign</button>
            </li>
        </ul>
    </div>

    <h2>Activity</h2>
    <div class="card">
        <ul>
            <li>
                <span>Team 01 uploaded Proposal_v2.docx</span>
                <span class="muted">10:42</span>
            </li>
            <li>
                <span>Team 02 started leader voting</span>
                <span class="muted">09:15</span>
            </li>
            <li>
                <span>MIAO Tung created Team 03</span>
                <span class="muted">Yesterday</span>
            </li>
            <li>
                <span>Requirement Analysis marked complete</span>
                <span class="muted">Yesterday</span>
            </li>
            <li>
                <span>LAI Ka-Yu invited CHUI Ka Chun to Team 02</span>
                <span class="muted">2 days ago</span>
            </li>
        </ul>
    </div>

</main>

</body>
</html>