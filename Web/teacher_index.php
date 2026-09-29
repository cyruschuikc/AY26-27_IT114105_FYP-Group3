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
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #fff;
            color: #222;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 200px;
            min-height: 100vh;
            background: black;
            color: white;
            padding: 22px 0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            align-self: flex-start;
        }

        .sidebar .brand {
            padding: 0 20px 22px;
            font-size: 19px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .sidebar a {
            display: block;
            padding: 10px 20px;
            color: #bbb;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .sidebar a:hover {
            color: white;
        }

        .sidebar a.active {
            color: white;
            font-weight: bold;
            border-left: 3px solid white;
            padding-left: 17px;
        }

        .sidebar a.logout {
            color: #ff8a8a;
            margin-top: auto;
        }

        .sidebar a.logout:hover {
            color: #ff5252;
        }

        /* Main */
        .main {
            flex: 1;
            padding: 35px 40px;
            max-width: calc(100% - 200px);
        }

        h1 {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 13px;
            color: #888;
            margin-bottom: 35px;
        }

        h2 {
            font-size: 15px;
            margin: 35px 0 12px;
            font-weight: bold;
        }

        h2:first-of-type {
            margin-top: 0;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th {
            text-align: left;
            font-weight: normal;
            font-size: 12px;
            color: #999;
            padding: 8px 10px 8px 0;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 12px 10px 12px 0;
            border-bottom: 1px solid #eee;
        }

        .muted {
            color: #999;
            font-size: 13px;
        }

        /* Plain list */
        ul {
            list-style: none;
            font-size: 14px;
        }

        ul li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        ul li:last-child {
            border-bottom: none;
        }

        /* Buttons - plain text links */
        .link {
            background: none;
            border: none;
            color: #222;
            font-size: 13px;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
        }

        .link:hover {
            color: #000;
            font-weight: bold;
        }

        .link.danger {
            color: #c62828;
        }

        @media (max-width: 700px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; position: static; }
            .main { max-width: 100%; padding: 25px 20px; }
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
            <td>Executing</td>
            <td><button class="link">View</button></td>
        </tr>
        <tr>
            <td>Team 02</td>
            <td>LAI Ka-Yu Savio</td>
            <td>4 / 4</td>
            <td>1 / 5</td>
            <td>Voting</td>
            <td><button class="link">View</button></td>
        </tr>
        <tr>
            <td>Team 03</td>
            <td>MIAO Tung</td>
            <td>3 / 4</td>
            <td>0 / 5</td>
            <td>Recruiting</td>
            <td><button class="link">View</button></td>
        </tr>
        <tr>
            <td>Team 04</td>
            <td class="muted">—</td>
            <td>2 / 4</td>
            <td class="muted">—</td>
            <td>Recruiting</td>
            <td><button class="link">View</button></td>
        </tr>
    </table>

    <h2>Unassigned Students</h2>
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

    <h2>Activity</h2>
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

</main>

</body>
</html>