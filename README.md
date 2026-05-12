# Student Works Management Plugin for Moodle

A comprehensive plugin for managing student academic works (course projects, internships, theses) in Moodle 5.1+ with modern interface and advanced features.

## Features

### For Students
- Upload works in PDF format
- Track review status in real-time
- Receive notifications on status changes
- View history of uploaded works
- Modern interface with dark theme support
- Responsive design for mobile devices

### For Teachers
- View all student works with filtering
- Filter by work type, status, student
- Search by topic and discipline
- Upload reviews and feedback
- Change work status via AJAX
- Export data to CSV/Excel formats
- Pagination for large work lists
- Dashboard with statistics

### Technical Features
- Full Moodle 5.1.3+ compatibility
- Modern Hooks API (Moodle 5.x)
- Optimized SQL queries (JOIN instead of N+1)
- Corporate UI 2025 (Glassmorphism, animations)
- Accessibility compliant (ARIA, keyboard navigation)
- Responsive mobile-first design
- Notification system
- Cron tasks for cleanup
- Multi-language support (English, Russian)
- File management via Moodle File API

## Requirements

- Moodle 5.1.3+ (2025041400)
- PHP 8.1+
- JavaScript enabled in browser
- File upload permissions
- Database with appropriate privileges

## Installation

1. Copy the `studentworks` folder to `[moodle]/local/studentworks/`
2. Log in as administrator
3. Navigate to "Site administration" → "Notifications"
4. Follow the installation instructions
5. Configure capabilities:
   - `local/studentworks:viewown` - for students to view their own works
   - `local/studentworks:viewall` - for teachers to view all works

## Upgrade

The plugin includes automatic upgrade scripts (`db/upgrade.php`). During upgrade:
- New database fields are added automatically
- Indexes and foreign keys are created
- Settings are updated
- Data migration is handled

## Project Structure

```
local/studentworks/
├── amd/src/studentworks.js          # Main JavaScript module
├── classes/
│   ├── export/exporter.php          # Data export functionality
│   ├── form/                        # Moodle form classes
│   │   ├── review_form.php          # Review form
│   │   └── studentwork_form.php     # Work submission form
│   ├── hooks/navigation.php         # Navigation hooks
│   ├── manager/work_manager.php     # Business logic and work management
│   ├── notification/notifier.php    # Notification system
│   ├── output/                      # Renderable output classes
│   │   ├── renderer.php             # Main renderer
│   │   ├── teacher_dashboard.php    # Teacher dashboard
│   │   ├── work_detail.php          # Work detail view
│   │   └── works_page.php           # Works listing page
│   └── task/cleanup_old_files.php   # Cron task for cleanup
├── db/
│   ├── access.php                   # Capability definitions
│   ├── hooks.php                    # Hook registrations
│   ├── install.xml                  # Database schema
│   ├── messages.php                 # Message providers
│   ├── tasks.php                    # Scheduled tasks
│   └── upgrade.php                  # Upgrade scripts
├── lang/                            # Language files
│   ├── en/local_studentworks.php    # English translations
│   └── ru/local_studentworks.php    # Russian translations
├── templates/                       # Mustache templates
│   ├── teacher_dashboard.mustache   # Teacher dashboard template
│   ├── work_detail.mustache         # Work detail template
│   ├── works_page.mustache          # Works listing template
│   └── upload_form.mustache         # Upload form template
├── index.php                        # Main student page
├── teacher.php                      # Teacher dashboard
├── upload.php                       # Work upload page
├── view.php                         # Work viewing page
├── lib.php                          # Library functions
├── styles.css                       # CSS styles
└── version.php                      # Plugin version
```

## Configuration

### Capabilities
- `local/studentworks:viewown` - View own works (students)
- `local/studentworks:viewall` - View all works (teachers)
- `local/studentworks:manage` - Manage works (administrators)

### Notifications
Ensure Moodle cron is configured:
```
* * * * * /usr/bin/php /path/to/moodle/admin/cli/cron.php
```

### Export Options
Teachers can export data in multiple formats:
- CSV (Excel compatible)
- Excel (if PHPExcel module available)
- JSON (for integrations)

## API Reference

### Work Manager
```php
use local_studentworks\manager\work_manager;

// Get works with filters
$works = work_manager::get_works([
    'userid' => $USER->id,
    'status' => 'reviewed',
    'type' => 'thesis'
], $page, $perpage);

// Update work status
work_manager::update_status($workid, 'reviewed', 'Excellent work!');

// Get statistics
$stats = work_manager::get_stats($userid);

// Export works data
$exportData = work_manager::export_works($filters, $format);
```

### JavaScript API
```javascript
require(['local_studentworks/studentworks'], function(studentworks) {
    // Initialize plugin
    studentworks.init();
    
    // Upload work
    studentworks.uploadWork(formData);
    
    // Update status
    studentworks.updateStatus(workId, newStatus, feedback);
});
```

## Database Schema

### local_studentworks_works
Main table storing work information:
- Work title and description
- File references
- Status (draft, submitted, reviewed, approved)
- Timestamps and user references

### local_studentworks_reviews
Stores teacher reviews and feedback.

### local_studentworks_notifications
Manages notification queue.

## Workflow

### Student Submission
1. Student navigates to works page
2. Clicks "Upload new work"
3. Fills work details and uploads PDF
4. Submits for review
5. Receives confirmation

### Teacher Review
1. Teacher accesses dashboard
2. Views list of submitted works
3. Opens work for review
4. Adds feedback and changes status
5. Student receives notification

## User Interface

### Student Interface
- Clean, intuitive upload form
- Status tracking with visual indicators
- Work history with timestamps
- Responsive design for all devices

### Teacher Interface
- Dashboard with statistics
- Advanced filtering and search
- Bulk operations
- Export functionality
- Real-time updates

## Security

- File upload validation (type, size)
- XSS protection for all inputs
- SQL injection prevention
- Role-based access control
- Session management

## Performance

- Database indexing for frequent queries
- Caching of frequently accessed data
- Pagination for large datasets
- Optimized file operations
- Lazy loading where appropriate

## Troubleshooting

### Common Issues
1. **File upload fails**: Check file size limits and permissions
2. **Notifications not sending**: Verify cron configuration
3. **Slow performance**: Check database indexes and server resources

### Debugging
- Enable Moodle debugging
- Check browser console for JavaScript errors
- Review Moodle logs
- Test with different user roles

## Additional Documentation

- `CHANGELOG.md` - Version history and changes
- `PERMISSIONS_FIX.md` - Permission configuration guide

## License

GNU GPL v3 or later

## Version Information

- Current version: 2.1.2
- Moodle compatibility: 5.1.3+
- Release status: Stable

## Contributing

Contributions are welcome! Please ensure:
1. Code follows Moodle coding standards
2. New features include tests
3. Documentation is updated
4. Security considerations are addressed
5. Performance implications are evaluated