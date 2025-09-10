# 🎓 Exam System - 391 Final Project

A comprehensive web-based examination system built with PHP, MySQL, Docker, HTML, CSS, and JavaScript. This system supports role-based access control for students, teachers, and administrators.

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose installed
- Git (for cloning/pushing to GitHub)

### Running the Application

1. **Clone the repository:**
   ```bash
   git clone https://github.com/afnanmz168/391-final-project.git
   cd 391-final-project
   ```

2. **Start the application:**
   ```bash
   docker compose up -d --build
   ```

3. **Access the application:**
   - Open your browser and go to: `http://localhost:8080`

4. **Default Admin Credentials:**
   - **Email:** admin@example.com
   - **Password:** admin123

### Stopping the Application
```bash
docker compose down
```

## 📊 Database Access & Viewing

### Method 1: Using Docker Exec (Recommended)
```bash
# Connect to MySQL container
docker exec -it examsystem-db-1 mysql -u exam_user -pexam_pass exam_system

# Common SQL queries
SHOW TABLES;
SELECT * FROM users;
SELECT * FROM exams;
SELECT * FROM questions;
SELECT * FROM submissions;
```

### Method 2: Using phpMyAdmin (Optional)
Add this service to your `docker-compose.yml`:
```yaml
phpmyadmin:
  image: phpmyadmin/phpmyadmin
  ports:
    - "8081:80"
  environment:
    PMA_HOST: db
    PMA_USER: exam_user
    PMA_PASSWORD: exam_pass
  depends_on:
    - db
```
Then access: `http://localhost:8081`

### Method 3: External MySQL Client
- **Host:** localhost
- **Port:** 3307
- **Username:** exam_user
- **Password:** exam_pass
- **Database:** exam_system

## 🏗️ System Architecture

### Database Schema
The system uses a normalized MySQL database with the following tables:

- **users** - User accounts (students, teachers, admins)
- **exams** - Exam definitions and metadata
- **questions** - Multiple-choice questions for exams
- **options** - Answer options for each question
- **submissions** - Student exam submissions
- **answers** - Individual question answers

### File Structure
```
ExamSystem/
├── docker-compose.yml          # Docker services configuration
├── Dockerfile                  # PHP-Apache container setup
├── db/
│   └── init.sql               # Database schema and initial data
├── includes/
│   ├── config.php             # Database connection
│   ├── auth.php               # Authentication functions
│   ├── helpers.php            # Utility functions
│   ├── header.php             # Common HTML header
│   └── footer.php             # Common HTML footer
├── public/
│   ├── assets/
│   │   ├── css/styles.css     # Application styling
│   │   └── js/app.js          # Interactive JavaScript
│   └── [page files]           # Application pages
└── README.md                  # This file
```

## 📄 Page Descriptions & Functionality

### Authentication Pages

#### `index.php` - Login Page
- **Purpose:** User authentication entry point
- **Features:** Login form, credential validation, role-based redirection
- **Access:** Public (unauthenticated users)

#### `register.php` - Registration Page
- **Purpose:** New user account creation
- **Features:** User registration with role selection (student/teacher)
- **Access:** Public (unauthenticated users)

#### `logout.php` - Logout Handler
- **Purpose:** Session termination and cleanup
- **Features:** Destroys user session, redirects to login
- **Access:** Authenticated users only

### Dashboard & Navigation

#### `dashboard.php` - Role-Based Dashboard
- **Purpose:** Main landing page after login
- **Features:**
  - **Students:** View available exams, recent results
  - **Teachers:** Manage created exams, view submissions
  - **Admins:** System overview, all exams and users
- **Access:** All authenticated users (content varies by role)

### Exam Management (Teachers/Admins)

#### `exams_create.php` - Create New Exam
- **Purpose:** Exam creation interface
- **Features:** Exam title, description, and initial setup
- **Access:** Teachers and Admins only
- **Workflow:** Creates exam → redirects to question addition

#### `exams_manage.php` - Exam Management Hub
- **Purpose:** Overview and control of all exams
- **Features:**
  - List all exams (with creator info for admins)
  - Publish/unpublish exams
  - Links to add questions
  - Exam status indicators
- **Access:** Teachers (own exams) and Admins (all exams)

#### `exam_add_question.php` - Question Builder
- **Purpose:** Add multiple-choice questions to exams
- **Features:**
  - Question text input
  - Multiple answer options (A, B, C, D)
  - Correct answer selection
  - Question preview and management
- **Access:** Exam creators and Admins
- **Database:** Inserts into `questions` and `options` tables

### Student Exam Interface

#### `exam_take.php` - Exam Taking Interface
- **Purpose:** Student exam participation
- **Features:**
  - Question navigation system
  - Real-time countdown timer
  - Auto-save functionality
  - Radio button answer selection
  - Automatic submission on timeout
- **Access:** Students only (for published exams)
- **JavaScript:** Timer, navigation, form validation

#### `submit_exam.php` - Exam Submission Handler
- **Purpose:** Process and grade exam submissions
- **Features:**
  - Automatic answer grading
  - Score calculation
  - Database transaction handling
  - Submission timestamp recording
- **Access:** Students (via form submission)
- **Database:** Inserts into `submissions` and `answers` tables

### Results & Analytics

#### `results.php` - Results Viewing
- **Purpose:** Display exam results and performance
- **Features:**
  - **Students:** Personal exam results and scores
  - **Teachers/Admins:** All submissions for their exams
  - Score percentages and detailed breakdowns
- **Access:** All authenticated users (filtered by role)
- **Database:** Queries `submissions` with joins to `exams` and `users`

## 🔐 Security Features

### Authentication & Authorization
- **Session Management:** Secure PHP sessions
- **Password Security:** bcrypt hashing
- **Role-Based Access:** Three-tier permission system
- **Input Validation:** SQL injection prevention
- **XSS Protection:** Output escaping

### Database Security
- **Prepared Statements:** All database queries use PDO prepared statements
- **Foreign Key Constraints:** Referential integrity with CASCADE deletes
- **User Isolation:** Dedicated database user with limited privileges

## 🎨 Frontend Features

### Responsive Design
- **Mobile-First:** Optimized for all screen sizes
- **Modern UI:** Clean, professional interface
- **Accessibility:** Proper form labels and semantic HTML

### Interactive Elements
- **Form Validation:** Real-time client-side validation
- **Exam Timer:** Visual countdown with warnings
- **Question Navigation:** Easy movement between questions
- **Loading States:** User feedback during operations
- **Confirmation Dialogs:** Prevent accidental actions

## 🔧 Development & Customization

### Adding New Features
1. **Database Changes:** Update `db/init.sql`
2. **Backend Logic:** Add PHP files in `public/`
3. **Styling:** Modify `public/assets/css/styles.css`
4. **Interactivity:** Update `public/assets/js/app.js`

### Environment Configuration
- **Database Settings:** Modify `docker-compose.yml` environment variables
- **PHP Configuration:** Update `Dockerfile` for PHP extensions
- **Application Config:** Edit `includes/config.php`

## 🐛 Troubleshooting

### Common Issues

1. **Port Already in Use:**
   ```bash
   # Change ports in docker-compose.yml
   ports:
     - "8081:80"  # Change 8080 to 8081
   ```

2. **Database Connection Failed:**
   ```bash
   # Check if containers are running
   docker compose ps
   
   # View logs
   docker compose logs db
   ```

3. **Permission Denied:**
   ```bash
   # Reset Docker volumes
   docker compose down -v
   docker compose up -d --build
   ```

### Logs and Debugging
```bash
# View application logs
docker compose logs web

# View database logs
docker compose logs db

# Follow logs in real-time
docker compose logs -f
```

## 📝 API Endpoints

While this is primarily a traditional web application, the following endpoints handle form submissions:

- `POST /index.php` - User login
- `POST /register.php` - User registration
- `POST /exams_create.php` - Create new exam
- `POST /exam_add_question.php` - Add question to exam
- `POST /submit_exam.php` - Submit exam answers
- `GET /exams_manage.php?action=publish&id=X` - Publish exam
- `GET /exams_manage.php?action=unpublish&id=X` - Unpublish exam

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes
4. Test thoroughly
5. Commit: `git commit -m "Add feature description"`
6. Push: `git push origin feature-name`
7. Create a Pull Request

## 📄 License

This project is created for educational purposes as part of a final project assignment.

## 👥 Authors

- **Developer:** Afnan Mazumder
- **Course:** 391 Final Project
- **Institution:** [Your Institution Name]

---

**Need Help?** Check the troubleshooting section above or review the application logs using the Docker commands provided.