# Tool Share Application

A Laravel-based tool sharing platform that enables users to rent and lend tools within their community.

## Features

### Tool Management
- Create, read, update, and delete tools
- Upload multiple tool images
- Set daily rental rates
- Manage tool availability status
- Categorize tools (Power Tools, Hand Tools, etc.)
- Track tool conditions

### User Features
- User authentication with Laravel Sanctum
- Tool ownership management
- Authorization policies for tool updates
- Image upload and management

### API Endpoints
- `POST /api/tools` - Create a new tool
- `PUT /api/tools/{id}` - Update tool details
- `GET /api/tools` - List all tools
- `GET /api/tools/{id}` - Get tool details
- `DELETE /api/tools/{id}` - Delete a tool

## Technology Stack
- PHP 8.1+
- Laravel 10.x
- MySQL/SQLite
- Laravel Sanctum for API authentication
- PHPUnit for testing
