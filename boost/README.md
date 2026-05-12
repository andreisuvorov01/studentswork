# Boost Theme for Moodle

A modern, responsive theme for Moodle based on Bootstrap 5 with enhanced features and improved user experience.

## Features

### Modern Design
- Based on Bootstrap 5 framework
- Responsive mobile-first design
- Dark mode support
- Accessibility compliant (WCAG 2.1)
- Modern UI components and animations

### Enhanced Navigation
- Drawer-based navigation system
- Course index with expandable sections
- Improved primary and secondary navigation
- Customizable menu system
- Breadcrumb navigation

### Performance Optimizations
- Optimized CSS and JavaScript
- Lazy loading of components
- Efficient resource loading
- Caching strategies
- Minified assets

### Customization
- SCSS-based theming system
- Customizable color schemes
- FontAwesome icon integration
- Template overrides
- Plugin compatibility

## Requirements

- Moodle 4.0+
- PHP 7.4+
- Modern web browser with JavaScript support
- SCSS compilation support (for development)

## Installation

### As a Theme
1. Copy the `boost` folder to `[moodle]/theme/boost/`
2. Log in as administrator
3. Navigate to "Site administration" → "Appearance" → "Themes" → "Theme selector"
4. Select "Boost" as the site theme
5. Configure theme settings

### Development Setup
1. Install Node.js and npm
2. Install Grunt: `npm install -g grunt-cli`
3. Navigate to theme directory: `cd [moodle]/theme/boost`
4. Install dependencies: `npm install`
5. Compile assets: `grunt css` and `grunt amd`

## Project Structure

```
theme/boost/
├── amd/src/                    # JavaScript modules
│   ├── bootstrap/              # Bootstrap JavaScript components
│   │   ├── dom/                # DOM manipulation utilities
│   │   ├── util/               # Utility functions
│   │   ├── alert.js            # Alert component
│   │   ├── button.js           # Button component
│   │   ├── modal.js            # Modal component
│   │   └── tooltip.js          # Tooltip component
│   ├── aria.js                 # ARIA accessibility
│   ├── drawer.js               # Drawer navigation
│   ├── drawers.js              # Multiple drawers management
│   ├── loader.js               # Loading indicators
│   └── toast.js                # Toast notifications
├── classes/                    # PHP classes
│   ├── output/                 # Output renderers
│   ├── privacy/                # Privacy API
│   ├── admin_settingspage_tabs.php  # Admin settings
│   ├── autoprefixer.php        # CSS autoprefixer
│   └── boostnavbar.php         # Navigation bar
├── cli/                        # Command-line tools
├── lang/                       # Language files
├── layout/                     # Page layouts
│   ├── columns1.php           # Single column layout
│   ├── columns2.php           # Two column layout
│   ├── drawers.php            # Drawer layout
│   ├── embedded.php           # Embedded layout
│   ├── login.php              # Login page layout
│   └── maintenance.php        # Maintenance mode layout
├── pix/                        # Images and icons
│   ├── fp/                    # File picker icons
│   ├── mod/                   # Module icons
│   └── favicon.ico            # Favicon
├── scss/                       # SCSS stylesheets
│   ├── bootstrap/             # Bootstrap SCSS source
│   ├── fontawesome/           # FontAwesome integration
│   ├── moodle/                # Moodle-specific styles
│   ├── preset/                # Theme presets
│   ├── bootstrap.scss         # Main Bootstrap import
│   ├── moodle.scss            # Main Moodle styles
│   └── preset.scss            # Preset styles
├── style/                      # Compiled CSS
├── templates/                  # Mustache templates
│   ├── core/                  # Core templates
│   ├── core_form/             # Form templates
│   ├── admin_setting_tabs.mustache    # Admin tabs
│   ├── drawer.mustache        # Drawer template
│   ├── navbar.mustache        # Navigation bar
│   └── primary-drawer-mobile.mustache # Mobile drawer
├── tests/                      # Test files
│   ├── behat/                 # Behavior tests
│   ├── privacy/               # Privacy tests
│   ├── boostnavbar_test.php   # Navigation tests
│   └── scss_test.php          # SCSS tests
├── config.php                  # Theme configuration
├── lib.php                     # Theme library
├── settings.php                # Theme settings
├── thirdpartylibs.xml          # Third-party libraries
└── version.php                 # Theme version
```

## Configuration

### Theme Settings
1. Navigate to "Site administration" → "Appearance" → "Themes" → "Boost"
2. Configure:
   - Color scheme and branding
   - Navigation settings
   - Layout options
   - Custom CSS/JavaScript
   - Font settings

### Presets
- Default preset: Standard Bootstrap styling
- Plain preset: Minimal styling
- Custom presets can be created in `scss/preset/`

## Customization

### SCSS Customization
1. Create custom SCSS file in `scss/preset/`
2. Import Bootstrap and Moodle variables
3. Override variables as needed
4. Compile with Grunt: `grunt css`

### Template Overrides
1. Copy template from `templates/` to local override location
2. Modify as needed
3. Clear theme cache

### JavaScript Customization
1. Create custom AMD module
2. Import in `amd/src/index.js`
3. Compile with Grunt: `grunt amd`

## Bootstrap Integration

### Updating Bootstrap
To update to the latest Bootstrap version:

1. Download Bootstrap source files
2. Remove old files: `theme/boost/scss/bootstrap/`
3. Copy new SCSS files from Bootstrap
4. Update JavaScript files in `amd/src/bootstrap/`
5. Update `thirdpartylibs.xml`
6. Run `grunt ignorefiles` to update linting rules
7. Compile assets: `grunt css` and `grunt amd`

### Bootstrap Components
The theme includes these Bootstrap components:
- Alerts and toasts
- Buttons and button groups
- Cards and modals
- Dropdowns and navigation
- Forms and validation
- Grid system and utilities
- Tooltips and popovers

## Accessibility

### ARIA Support
- Proper ARIA labels and roles
- Keyboard navigation support
- Screen reader compatibility
- Focus management
- Color contrast compliance

### WCAG Compliance
- AA level compliance
- Text alternatives for images
- Logical reading order
- Resizable text support
- No keyboard traps

## Performance

### Optimization Techniques
- Critical CSS inlining
- JavaScript deferred loading
- Image optimization
- Font subsetting
- Cache headers

### Build Process
- SCSS compilation with autoprefixing
- JavaScript module bundling
- Asset minification
- Source maps for debugging

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari, Chrome for Android)

## Development

### Prerequisites
- Node.js 14+
- npm 6+
- Grunt CLI
- PHP 7.4+
- Moodle development environment

### Build Commands
```bash
# Install dependencies
npm install

# Compile CSS
grunt css

# Compile JavaScript
grunt amd

# Watch for changes
grunt watch

# Run tests
grunt test
```

### Testing
- PHPUnit tests for PHP components
- Behat tests for user interactions
- SCSS compilation tests
- JavaScript unit tests

## Troubleshooting

### Common Issues
1. **Styles not applying**: Clear theme cache and browser cache
2. **JavaScript errors**: Check browser console and compile with `grunt amd`
3. **SCSS compilation errors**: Verify SCSS syntax and dependencies
4. **Layout issues**: Check Bootstrap grid usage and responsive classes

### Debugging
- Enable Moodle debugging
- Check browser developer tools
- Review theme cache
- Test with different browsers

## Integration

### With Plugins
- Compatible with most Moodle plugins
- Plugin-specific style overrides
- JavaScript event integration
- Template block support

### With Custom Code
- Custom SCSS variables
- Additional JavaScript modules
- Template modifications
- Layout overrides

## License

GNU GPL v3 or later

## Version Information

- Based on Bootstrap 5.x
- Moodle compatibility: 4.0+
- Current version: Included with Moodle core
- Development status: Actively maintained

## Contributing

Contributions are welcome! Please:
1. Follow Moodle coding standards
2. Include tests for new features
3. Update documentation
4. Test with different browsers and devices
5. Consider accessibility implications