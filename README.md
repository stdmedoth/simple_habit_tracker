# Habit Tracker

This is a simple Habit Tracker application built with PHP and SQLite. It allows users to create and track habits, set success and failure goals, and monitor progress on a daily basis. The project uses an SQLite database to store habit data and track progress over time.

## Features

- **Create New Habits:** Users can add new habits with a name, description, success goal, and failure threshold.
- **Track Progress:** Users can update their daily progress by marking successes or failures for each habit.
- **View Habit Progress:** A detailed view of each habit’s progress for the current month.
- **Delete Habits:** Users can delete any habit they no longer wish to track.

## Installation

To run this project locally, follow these steps:

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/habit-tracker.git
cd habit-tracker
```

### 2. Set up the Database

This project uses SQLite for database management. The database (`habits.db`) is created automatically when the application runs, but you can initialize it manually if needed by running the provided SQL commands.

### 3. Set up the Web Server

Ensure you have a local PHP server running. You can use the built-in PHP server for testing:

```bash
php -S localhost:8000
```

Then, navigate to [http://localhost:8000](http://localhost:8000) in your web browser.

## Usage

### Creating Habits

To create a new habit, fill out the form at the top of the page with the habit's name, description, success goal, and failure threshold. Click the "Add Habit" button to save it.

### Updating Progress

For each habit, you can update your daily progress by marking successes or failures. Use the respective buttons to log progress.

### Viewing Progress

The "Monthly Progress" section shows a summary of successes and failures for each habit throughout the month. Each day will be represented by a cell in a table, with a checkmark for success, a cross for failure, and an empty cell if no progress was made.

### Deleting Habits

To delete a habit, click the "Delete" link next to the habit. A confirmation prompt will appear before the habit is removed.

## Database Schema

### Habits Table

| Field         | Type         | Description                                  |
|---------------|--------------|----------------------------------------------|
| `id`          | INTEGER      | Primary key                                  |
| `name`        | TEXT         | Name of the habit                           |
| `description` | TEXT         | Description of the habit                    |
| `completed`   | INTEGER      | Indicates whether the habit is completed (0 or 1) |
| `success_goal`| INTEGER      | The number of successes needed               |
| `failure_goal`| INTEGER      | The failure threshold for the habit          |
| `created_at`  | DATETIME     | Timestamp when the habit was created        |

### Habit Progress Table

| Field        | Type         | Description                                |
|--------------|--------------|--------------------------------------------|
| `id`         | INTEGER      | Primary key                                |
| `habit_id`   | INTEGER      | Foreign key referencing the `habits` table |
| `date`       | DATE         | Date of the progress record               |
| `successes`  | INTEGER      | Number of successes on the specified date |
| `failures`   | INTEGER      | Number of failures on the specified date  |

## Contributions

Contributions are welcome! Please fork the repository and submit a pull request with your changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.