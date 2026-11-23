# TaskFlow — Smart Task Management System

A responsive **full-stack task management web application** built with **PHP, Vanilla JavaScript, CSS and SQLite**, developed by **Amir Syahmi @ merlylicious** as a software engineering portfolio project.

## Features

- Create, edit and delete tasks
- Task descriptions, priority (Low / Medium / High), due dates and status (To Do / In Progress / Completed)
- Live overview cards: total, in progress, completed and overdue
- Filter by priority, search by title or description, sort by priority, due date, newest or alphabetical
- Dedicated Due Today, Upcoming and Completed views
- Completion progress indicator
- Mobile-responsive dark dashboard interface
- Persistent SQLite storage (no external database server needed)
- JSON API with parameterized SQL queries and input validation

## Screenshots

Add screenshots to `docs/` after running the application, then include them here.

## Requirements

- PHP **8.1+** with extensions `pdo_sqlite` and `mbstring` enabled
- A modern browser
- Write permission to `data/`

## Quick Start

```bash
# Clone your repository
 git clone https://github.com/merlylicious/Smart-Task-Management-System.git
 cd Smart-Task-Management-System

# Start PHP development server
 php -S localhost:8000
```

Open **http://localhost:8000** in your browser. The SQLite database is created automatically on first use.

> On Windows with XAMPP: Enable `extension=pdo_sqlite` and `extension=mbstring` in the active `php.ini`, restart Apache, and place the project folder under `htdocs` (or use the PHP CLI server). Verify enabled extensions with `php -m`.

## Folder Structure

```text
Smart-Task-Management-System/
├── api/
│   └── tasks.php         # JSON CRUD API
├── assets/
│   ├── app.js            # UI and API requests
│   └── style.css         # Responsive dashboard styling
├── data/
│   └── .gitkeep          # SQLite database stored here at runtime
├── lib/
│   └── bootstrap.php     # Database setup and input validation
├── index.php             # Application entry point
├── .gitignore
└── README.md
```

## API Reference

All endpoints use `api/tasks.php`:

| Method | Action | Body |
|---|---|---|
| GET | List all tasks | none |
| POST | Create a task | `title`, `description`, `status`, `priority`, `due_date` |
| PUT | Edit a task | All task fields plus `id` |
| DELETE | Delete a task | `id` |

Write requests require JSON (`Content-Type: application/json`) and a same-origin browser request.

Example request body:

```json
{
  "title": "Review database design",
  "description": "Check relationships and constraints",
  "status": "in_progress",
  "priority": "high",
  "due_date": "2026-12-01"
}
```

## Security Notes

- Prepared statements help prevent SQL injection.
- The UI escapes user-provided text before rendering HTML.
- Server-side validation restricts allowed statuses, priorities and input lengths.
- Same-origin checks are applied to writes.
- **This is a single-user local portfolio demonstration**, not an internet-ready multi-user SaaS product. It does not include authentication, user accounts or production-grade CSRF protection. Do not deploy publicly with sensitive data without implementing those controls.
- Run PHP with the project root as document root **only for local development**. For production, place `data/` and `lib/` outside the public web root or explicitly deny direct web access to them.

## Roadmap

- [ ] User authentication and role-based permissions
- [ ] Team workspaces and collaboration
- [ ] Recurring tasks and reminders
- [ ] Automated unit and integration tests
- [ ] Calendar view

## Author

**Amir Syahmi @ merlylicious** — [GitHub: merlylicious](https://github.com/merlylicious)

## License

MIT License. See [LICENSE](LICENSE).
