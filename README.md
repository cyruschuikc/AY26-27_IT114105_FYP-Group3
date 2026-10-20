# AY26-27_IT114105_FYP-Group3

## ⚖️License
All the materials in this repository is licensed under the Unlicense License.

The Unlicense license with no conditions whatsoever which dedicates works to the public domain. \
Unlicensed works, modifications, and larger works may be distributed under different terms and without source code.

|Permissions|Limitations|Conditions|
|-|-|-|
|Private use✅|Liability❌| |
|Commercial use✅|Warranty❌| |
|Modification✅| | |
|Distribution✅| | |

## Developing Tools
### Android Studio
**Quick Download here ⬇️：**
|[Panda4（recommend）](https://edgedl.me.gvt1.com/android/studio/install/2025.3.4.6/android-studio-panda4-windows.exe)|[Quail4](https://developer.android.com/studio?hl=zh-tw)|
|-|-|
### VS Code
> [!NOTE]
> Go to Microsoft Store download
### XAMPP & xDebug
[Install XAMPP and xDebug.pdf](https://github.com/user-attachments/files/32568564/Install.XAMPP.and.xDebug.pdf)
#### XAMPP
**Quick Download here ⬇️：**
|[7.3.27-1（recommend）](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.27/xampp-portable-windows-x64-7.3.27-1-VC15-installer.exe/download)|[7.3.27-0](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.27/xampp-portable-windows-x64-7.3.27-0-VC15-installer.exe/download)|[7.3.26-1（recommend）](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.26/xampp-portable-windows-x64-7.3.26-1-VC15-installer.exe/download)|[7.3.26-0](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.26/xampp-portable-windows-x64-7.3.26-0-VC15-installer.exe/download)|
|-|-|-|-|
|[7.3.25-1（recommend）](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.25/xampp-portable-windows-x64-7.3.25-1-VC15-installer.exe/download)|[7.3.25-0](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.25/xampp-portable-windows-x64-7.3.25-0-VC15-installer.exe/download)|[7.3.24（recommend）](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.24/xampp-portable-windows-x64-7.3.24-0-VC15-installer.exe/download)|[7.3.23-0（recommend）](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/7.3.23/xampp-portable-windows-x64-7.3.23-0-VC15-installer.exe/download)|

- [Click here to download other version](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/)
#### xDebug
[Quick download xDebug｜php_xdebug-2.9.8-7.3-vc15.dll](http://xdebug.org/files/php_xdebug-2.9.8-7.3-vc15.dll)
### PhpStorme
### MySQL

## Teacher project module

Teachers can create, edit, duplicate and publish projects from
`Web/teacher_projects.php` → **Create Project**.

| Feature | Where |
|---|---|
| Project details, requirements, course, attachments | `Web/teacher_project_create.php` |
| Team rules: free / random / teacher-assigned, cross-class, leader permissions, post-deadline action | `Web/teacher_project_create.php` |
| Unlimited milestones and deadline reminder setting | `Web/teacher_project_create.php` |
| Announcement preview, in-app notifications, send now or schedule | `Web/teacher_project_create.php` |
| Draft / published state, duplicate project | `Web/teacher_projects.php` |
| Team formation progress board and extension request review | `Web/project_detail.php` |
| Scheduled announcements and deadline reminders | `Web/project_cron.php` |

Run the scheduler every few minutes (Windows Task Scheduler or cron):

```
C:\xampp\php\php.exe C:\xampp\htdocs\AY26-27_IT114105_FYP-Group3\Web\project_cron.php
```

Project attachments are stored in `Web/uploads/project_attachments/`.
The complete schema, seed data, constraints and triggers are consolidated in
the root-level `teambox.sql`. Import that single file into a fresh `teambox`
database; no separate migration SQL files are required.

Announcements are delivered through in-app notifications only.

## Teacher group chat

The teacher-side chat has its own two-pane inbox:

| File | Purpose |
|---|---|
| `Web/teacher_messages.php` | Conversation list and message thread |
| `Web/teacher_chat_thread.php` | Sends a text message |
| `Web/teacher_chat_feed.php` | JSON feed polled every 5 seconds |
| `Web/teacher_chat_helpers.php` | Shared access, message and read-state queries |

Teacher messages are stored in the existing `messages` table with
`sender_user_id` set and `sender_id` left NULL. Student messages keep using
`sender_id`. The complete chat schema is included in the root-level
`teambox.sql`.

- Tick **Pin as group announcement** before sending to keep a notice above the
  conversation. Pinned notices can be unpinned from the banner.
- Unread student messages containing `@Teacher Name` appear in the notification
  bell under **Mentions**.
- A group with a pending row in `group_requests` is shown as
  **Pending Approval** in the Teams list.
- Chat conversations only appear for groups in `Voting` or `Executing` with no
  pending formation request. Accepting a request moves a `Recruiting` or
  `Waiting_Send` group to `Voting`.

## Teacher files view

Recent Uploads lists task attachments only (shared-area files are excluded)
and groups versions by file name, team, uploader and task:

- one row per file group, showing the latest upload
- `vN` chip opens the version history
- `Resubmitted` appears when the latest upload is newer than the last rejection
- Review Center reviews each task attachment individually; shared-area files
  are excluded there too

## Teacher teams view

The Teams page uses attention-first team cards instead of a wide table:

- summary: total teams, awaiting approval, active teams and overdue tasks
- status chips with counts and search by team, project or leader
- progress bar, last activity and reason chips on each team
- actions for View, Review request and Message team

## Teacher team detail

- only the project supervisor can open a team detail page
- the Project card shows course, supervisor, period, deadline countdown and
  the current milestone
- pending group requests show **Pending Approval** and a review action
- task progress and last activity are shown without the old "today" label bug
- the Tasks list shows due status, attachment count and status
- the main Tasks table shows open work only; completed tasks are in a
  collapsed section and overdue rows use a light red background
- only task attachments are listed; shared-area files are excluded
- member activity counts task files, not shared uploads
- Activity uses avatars, links and filters, merges consecutive uploads, shows
  the latest five entries with **Show more**, and keeps deletions inside a
  collapsed **System cleanup** section
- Activity filters run in the browser without reloading or jumping to the top
- Project and Team Overview sit side by side at equal height on wide screens;
  Members use a horizontal switcher below them and stack on narrow screens

### Team approval rules

- Teacher milestones are project-level and read-only for every team.
- Student tasks belong to a single group.
- Pending-approval teams can view project milestones only; team tasks,
  attachments, activity and chat stay locked until approval.

## Teacher dashboard

The dashboard is action-focused:

- **Needs Attention** links to pending reviews, group requests, overdue tasks
  and students with no uploads
- **Upcoming Deadlines** merges tasks and milestones, including overdue items
- **Group Formation** summarises recruiting, waiting approval, voting and
  executing teams
- **Teams Needing Attention** lists only groups that require action
- Recent Activity is limited to the teacher's own projects

The "Phases Not Submitted" card counts project milestones whose end date is
today or earlier and whose status is not `Completed`.

Milestones do not currently store a completion timestamp and tasks are not yet
linked to a milestone, so the dashboard can show overdue phases but cannot
prove whether a completed phase was submitted on time.
