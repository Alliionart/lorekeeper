# Extension: Teams
![Static Badge](https://img.shields.io/badge/Version-V3.0.0-blue)
![Static Badge](https://img.shields.io/badge/Locally_tested-yes-green) ![Static Badge](https://img.shields.io/badge/Live_Tested-Yes-green)


In the event youve found yourself here without intending to find this extension, go here: [Base Lorekeeper](https://github.com/lk-arpg/lorekeeper)

This extension adds team/departments support for staff and admin teams.this includes:
- The ability to create, edit, and manage staff teams. Each team can be categorized as main, leadership, or admin account teams.
- The ability to give staff users a team assignment and give them different roles (Lead, Primary, Secondary, Trainee). 
- The ability to toggle if a team is open to staff applications
- A staff/admin application function, allowing members to apply to the team and for staff to either approve or deny them. 
- A new staff power called "edit_teams" which controls which staff ranks can edit team data or add users to teams. 
- Two new settings, "is_applications_comment_read_only", which toggles application comment chains to a read only state. and "notify_staff_applicants" which toggles if a application approval or denial notifies the user. 
- Two new public pages that shows the current teams and who works in them, and a page showing which teams exist and which have open applications.
- The admin panel now features a section at the bottom listing staff responsibilities by team.


# How to install

- Pull the branch and fix any merge conflicts (all changes to this readme file can be ignored)
- `php artisan migrate`
- `php artisan add-site-settings`
- `php artisan add-text-pages`
- `php artisan update-extension-tracker`
- Use the admin panel (logged in as the admin user) to add the needed "edit-teams" power to any ranks you want.
- Set up teams using the team manager
- To edit the "Join the team" page intro text, use the admin panel to edit the text page this extension adds (I recommend adding any application specific text for your team here, including a application form)

# Bug Reporting and making Suggestions

If youve found a bug, you can submit a PR, make an Issue, or DM me on discord (FallingFireX). Pease be sure to take screenshots or save snips from your error logs (this doesnt apply to PRs that directly fix the issue)! I am NOT a formally trained web developer, please be patient with bugs and bug fixes; there probably will be a few.

Making a suggestion can be done directly in my dms. Please be aware im not a very socially energetic person; I might not reply but I HAVE seen your message.

# Images
Teams page from a live site: (credit: Kukuri-arpg)
![Teams page](https://i.gyazo.com/fa2c976419d9c27f1ae58cbc4e32390d.png)

![team info page](https://i.gyazo.com/90a1f1f2e4b42f5ba42e2ed17edbf8e1.png)
![team index](https://i.gyazo.com/7958ccf41a6f71e35c93aef3edf55065.png)
![admin panel team responsibilities](https://i.gyazo.com/90e44243b2e4e57e1cd710c10bff5921.png)
![Edit a user](https://i.gyazo.com/3bf546e8ef9129cbd81cad24d5750dcc.png)
![Application](https://i.gyazo.com/0408507d658f0154bef3c11e40c4102f.png)