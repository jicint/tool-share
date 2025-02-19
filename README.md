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
## Setup Instructions

1. Clone the repository

2. Install dependencies
3. Configure environment

4. Configure database in `.env`

5. Run migrations

6. Start the server

## Testing

Run all tests:
bash
php artisan test
Run specific test suite:


## Database Schema

### Tools Table
- id (primary key)
- user_id (foreign key)
- name
- description
- category
- daily_rate
- condition
- availability_status
- timestamps

### Tool Images Table
- id (primary key)
- tool_id (foreign key)
- image_path
- order
- timestamps

## Contributing
1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License
MIT License

## Authors
- Your Name (@yourgithub)

## Acknowledgments
- Laravel Team
- Contributors
