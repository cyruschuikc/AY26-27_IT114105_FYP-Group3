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
The schema changes are in `Web/sql/2026_10_20_project_module.sql` and are also
appended to `teambox.sql`.

Announcements are delivered through in-app notifications only.
