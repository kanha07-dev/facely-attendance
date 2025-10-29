# Facely Attendance System

A web-based attendance management system using face recognition technology built with PHP, JavaScript, and TensorFlow.js.

## Features

- **Face Recognition**: Automated attendance marking using facial recognition
- **Multi-role System**: Admin, Teacher, HOD, and Student roles
- **Real-time Attendance**: Live attendance tracking and reporting
- **Student Management**: Add, edit, and manage student profiles
- **Subject Management**: Assign subjects to teachers and streams
- **Attendance Reports**: View and export attendance data
- **Secure Authentication**: Role-based access control

## Project Structure

```
facely-attendance/
│
├── admin/                    # Admin panel
│   ├── auth/                 # Admin authentication
│   ├── hod/                  # HOD specific functions
│   ├── management/           # Subject and stream management
│   ├── teacher/              # Teacher functions
│   ├── users/                # User management
│   └── img/uploads/          # Admin uploaded images
│
├── assets/                   # Static assets
│   ├── css/                  # Stylesheets
│   └── js/                   # JavaScript files
│
├── auth/                     # Authentication system
├── config/                   # Configuration files
├── includes/                 # Reusable components
├── models/                   # TensorFlow.js models
├── public/                   # Public pages
├── utils/                    # Utility functions
│
├── img/uploads/              # User uploaded face images
├── db.sql                    # Database schema
├── index.php                 # Main entry point
└── README.md                 # This file
```

## Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/yourusername/facely-attendance.git
   cd facely-attendance
   ```

2. **Set up the database:**

   - Import `db.sql` into your MySQL database
   - Update database credentials in `config/config.php`

3. **Configure the application:**

   - Copy `config/config.php` and update with your database details
   - Ensure web server has write permissions for `img/uploads/` directories

4. **Install dependencies:**

   - Ensure PHP 7.4+ is installed
   - Enable required PHP extensions: mysqli, gd, mbstring

5. **Access the application:**
   - Open your browser and navigate to the project URL
   - Default admin credentials: Check `db.sql` or create via setup

## Usage

### For Students:

- Register with face capture
- Login and view attendance records

### For Teachers:

- Mark attendance using face recognition
- View assigned subjects and students

### For Admins/HODs:

- Manage users, subjects, and streams
- View comprehensive attendance reports
- Configure system settings

## Technologies Used

- **Backend**: PHP 7.4+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Face Recognition**: TensorFlow.js
- **Database**: MySQL
- **Web Server**: Apache/Nginx

## Security Notes

- Never commit `config/config.php` to version control
- Regularly backup uploaded images and database
- Use HTTPS in production
- Keep PHP and dependencies updated

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support or questions, please open an issue on GitHub.
