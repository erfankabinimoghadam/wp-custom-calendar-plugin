# Project Overview:
This project was developed for a client who required a highly specific calendar solution integrated into their WordPress site. The client needed a simple, intuitive calendar capable of displaying events and courses pulled from existing pages on their website, while also allowing custom events to be added manually. Automatic events and manual events are visually distinct, and each event can generate a modal with detailed information, subscription links, or a direct link to the original page. Users can filter which types of events are displayed on the calendar, giving full control over the view.

The solution was implemented using FullCalendar.js on the frontend, while the WordPress admin dashboard provides a custom interface to manage events. This allows the client to handle all event management without touching any code.

## Features:
- Displays both automatically generated events from pages and manually added events from the admin panel
- Automatic modals for events showing descriptions, metadata, subscription links, or links to original pages
- Ability to filter events by type, including Courses, Events, or All
- Distinguishes manual versus automatic events visually
- Fully responsive for different screen sizes
- Admin panel allows adding, editing, and deleting manual events
- Rich text support for event descriptions using WordPress editor
- Security implemented with nonces and capability checks

## Architecture:
The plugin is organized into clear components for maintainability. The admin panel handles all CRUD operations for manual events and displays existing courses in a table. The frontend shortcode outputs the calendar HTML and passes event data to JavaScript. The JavaScript layer initializes FullCalendar.js, manages modals, renders events, and handles filtering. Helper functions fetch and normalize event data from multiple sources, process metadata, and provide utilities for date formatting and other display logic.

## Technical Highlights:
The plugin uses the WordPress Options API to store manual events and integrates FullCalendar.js for interactive calendar functionality. Automatic and manual events are clearly distinguished, and modals display detailed information with optional links. Security is built in with proper capability checks and nonces for admin actions. The code is structured to separate backend, frontend, and JavaScript logic, making it maintainable and extendable.

## Notes:
This plugin is custom-built for the client and is not intended for public distribution. All variable names, class names, and page-specific data are project-specific and can be replaced with placeholders for use in other projects. The focus is on functionality, interactivity, and ease of content management rather than visual aesthetics.
