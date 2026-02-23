# Foundation University - Student Organization Management System (FU SOMS)

## About FU SOMS

The **Foundation University Student Organization Management System (FU SOMS)** is a comprehensive web-based platform designed to streamline and manage student organization activities, events, and workflows at Foundation University.

The system facilitates:

- **Event Management** - Create, track, and manage student organization events from proposal to completion
- **Multi-level Approval Workflow** - Automated routing through different approval levels (Club Adviser → SAO → OSL → Vice Chancellor → OUC)
- **Collaboration Tracking** - Manage inter-organizational collaborations and partnerships
- **Budget Management** - Detailed budget breakdown and tracking for each event
- **Point System** - Automated points allocation for organizational activities
- **Documentation Repository** - Centralized storage for event proposals, programs, and documentation
- **Automated Status Updates** - Scheduled tasks to update event statuses based on dates

## Getting Started

This guide will help you set up the FU SOMS project on your local machine for development and testing purposes.

### Prerequisites

Before you begin, make sure you have the following installed:

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- Composer
- A web server (Apache, Nginx, or PHP's built-in server)

### Technology Stack

- **Framework**: CodeIgniter 4
- **Database**: MySQL/MariaDB
- **Authentication**: Google OAuth 2.0
- **PHP Dependencies**: Managed via Composer

## Database Setup

This repository includes migrations and seeders for easy database setup.

## Database Structure

### Migrations (Database Tables)

All migration files create your database tables in the correct order:

1. `access_level` - User access levels
2. `organization_type` - Organization types
3. `organization` - Organizations
4. `users` - User accounts
5. `department` - Academic departments
6. `events_status` - Event status types
7. `events` - Main events table
8. `collaboration` - Event collaborations
9. `events_history` - Event history/audit trail
10. `event_budget_breakdown` - Budget details
11. `point_system` - Points allocation system
12. `event_points` - Event points
13. `event_uploads` - Event file uploads
14. `event_documentation` - Event documentation
15. **MySQL Event Scheduler** - Automatically updates expired events daily at 5pm

### Seeders (Initial Data)

Seeders populate tables with initial/required data:

- `AccessLevelSeeder` - Student, Club Adviser, SAO, OSL, Vice Chancellor, OUC
- `DepartmentSeeder` - Computer Science, Education, Engineering
- `OrganizationTypeSeeder` - Academic, Non-Academic, Cultural, Sports, Religious
- `EventsStatusSeeder` - Pending, In-Progress, Awaiting Documentation, etc.
- `PointSystemSeeder` - Points allocation rules
- `DatabaseSeeder` - Master seeder that runs all others

## Installation

Follow these steps to set up the project on your local machine:

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/fu-soms.git
cd fu-soms
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Google OAuth Client

This project uses Google OAuth for authentication:

```bash
composer require google/apiclient:^2.13
```

### 4. Configure Environment

Copy the example environment file and configure it:

```bash
cp env .env
```

Edit the `.env` file and configure your database settings:

```env
database.default.hostname = localhost
database.default.database = foundationu_soms
database.default.username = your_mysql_username
database.default.password = your_mysql_password
database.default.DBDriver = MySQLi
```

### 5. Create the Database

**IMPORTANT**: You must manually create the database before running migrations.

**Option 1: Using phpMyAdmin**

- Open phpMyAdmin
- Click "New" in the left sidebar
- Enter database name: `foundationu_soms`
- Choose collation: `utf8mb4_general_ci`
- Click "Create"

**Option 2: Using MySQL Command Line**

```sql
CREATE DATABASE foundationu_soms CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

**Option 3: Using MySQL Client**

```bash
mysql -u your_username -p
CREATE DATABASE foundationu_soms;
exit;
```

### 6. Run Database Migrations

Run all migrations to create the database structure:

```bash
php spark migrate
```

### 7. Seed the Database

Populate the database with initial required data:

```bash
php spark db:seed DatabaseSeeder
```

### 8. Enable MySQL Event Scheduler

The system uses MySQL's event scheduler to automatically update expired events. Verify it's enabled:

```sql
SHOW VARIABLES LIKE 'event_scheduler';
```

If it shows OFF, enable it:

```sql
SET GLOBAL event_scheduler = ON;
```

### 9. Start the Development Server

```bash
php spark serve
```

The application will be available at `http://localhost:8080`

## MySQL Event Scheduler

The migration creates a MySQL Event that runs **daily at 5:00 PM** and automatically:

- Checks all events where `event_end_date` has passed
- Updates `status_id` to `4` (For Verification)
- Only for events with `status_id = 2` (In-Progress)

### Important Notes on Event Scheduler:

- Some shared hosting providers disable the MySQL Event Scheduler
- Make sure to enable it in production: `SET GLOBAL event_scheduler = ON;`
- This setting may need to be added to your MySQL configuration file (`my.cnf` or `my.ini`) to persist after server restarts:
  ```
  [mysqld]
  event_scheduler=ON
  ```

## Database Structure

## Useful Commands

**Check migration status:**

```bash
php spark migrate:status
```

**Refresh database (rollback all and re-migrate):**

```bash
php spark migrate:refresh
```

**Refresh and seed:**

```bash
php spark migrate:refresh --seed
```

## Contributing

When contributing to this repository, please ensure:

1. All migrations are properly tested
2. Seeders contain accurate initial data
3. Database changes are documented
4. Follow the existing code structure and naming conventions

### Committing Database Changes

Database migrations and seeders are tracked in version control:

```bash
git add app/Database/Migrations/
git add app/Database/Seeds/
git commit -m "Add/Update database migrations and seeders"
git push
```

**Important**: Never commit:

- `.env` file (contains sensitive credentials)
- Actual database files or dumps with real data
- User passwords or API keys

## Troubleshooting

**Problem:** Foreign key constraint fails
**Solution:** Make sure migrations run in the correct order (they're numbered for this reason)

**Problem:** Event scheduler not running
**Solution:** Check if it's enabled: `SHOW VARIABLES LIKE 'event_scheduler';`

**Problem:** Seeder already inserted data
**Solution:** You can truncate tables before re-seeding or use `db:seed --force`

## Authors & Contributors

This project was created and developed by:

### Core Development Team

- **[Johnny Xavier Obar]** - _Lead Developer_ - [GitHub Profile](https://github.com/XaviDaSloth)
- **[Allen Gabrielle Villas]** - _Backend Developer/Database Designer_ - [GitHub Profile](https://github.com/allengabriellevillas-lab)
- **[Kieanne James Paco]** - _Frontend/Backend Developer_ - [GitHub Profile](https://github.com/kieanne)
- **[Mich Angela Pinili]** - _Frontend Developer/ UI/UX Designer_ - [GitHub Profile](https://github.com/michpinili08)
- **[Kenny Dee Amorgiente]** - _Backend Developer_ - [GitHub Profile](https://github.com/kennydeeamorgiente-arch)
- **[Kenny Jay Amorgiente]** - _Frontend Developer_ - [GitHub Profile](https://github.com/kennyjayamorgiente-ux)

### Special Thanks

- Foundation University and the College of Computer Studies for supporting this project
- [Percival Carino] - Project Adviser
- [Sheena Mae Sabado] - College of Computer Studies Dean

## License

This project is developed for Foundation University. All rights reserved.

## Contact

For questions or support regarding this project, please contact:

- **Institution**: Foundation University

---

**Foundation University - Student Organization Management System**  
_Streamlining student organization management and event workflows_
