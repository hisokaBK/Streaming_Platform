# Streaming Platform

A modern live streaming platform built with **Laravel** and **Vue.js**, designed to support real-time interaction, replay videos, user engagement, and scalable API-first architecture.

## Overview

**Streaming Platform** is a full-stack web application that allows creators to go live, interact with viewers in real time, and automatically turn finished live sessions into replayable videos.

The project was developed as a **first-year final project at YouCode (UM6P)** and focuses on combining live broadcasting, audience engagement, and content persistence in one platform.

## Key Features

- **Authentication & Authorization**
  - Secure authentication with Laravel Sanctum
  - Role-based access control for users and admins

- **Live Streaming**
  - Stream creation and management
  - Live rooms powered by LiveKit
  - Real-time viewer interaction during live sessions

- **Replay Videos**
  - Automatic replay generation after a stream ends
  - Video listing and detail pages
  - Thumbnail and media handling

- **Real-Time Interaction**
  - Live comments
  - Live reactions
  - Real-time notifications
  - Messaging between users

- **User Profiles**
  - Public and private profile pages
  - Avatar and background image management
  - Followers / following system
  - User content preview (live streams and videos)

- **Content Organization**
  - Categories for streams and videos
  - Filtering and browsing experience

- **Admin Features**
  - Admin dashboard
  - User and platform management

## Tech Stack

### Backend
- **Laravel**
- **Laravel Sanctum**
- **Laravel Reverb / Broadcasting**
- **PostgreSQL**
- **LiveKit**
- **Docker**

### Frontend
- **Vue.js**
- **Vue Router**
- **Axios**
- **Tailwind CSS**

### DevOps / Tools
- **Docker Compose**
- **Nginx**
- **pgAdmin**

## Architecture

The project follows an **API-first architecture**:

- **Backend** exposes a REST API with Laravel
- **Frontend** consumes the API using Vue.js
- **LiveKit** handles real-time media streaming
- **Reverb / WebSockets** handle real-time events such as reactions and notifications
- **PostgreSQL** stores application data

## Main Modules

- Authentication
- Profiles
- Streams
- Replay Videos
- Comments
- Reactions
- Categories
- Subscriptions
- Messaging
- Notifications
- Admin Dashboard

## Project Structure

```bash
Streaming_Platform/
├── backend/              # Laravel API
├── frontend/             # Vue.js application
├── docker/               # Docker configuration files
├── docker-compose.yml    # Multi-container setup
└── README.md
```

## Installation

### 1. Clone the repository

```bash
git clone <your-repository-url>
cd Streaming_Platform
```

### 2. Start Docker services

```bash
docker compose up -d --build
```

### 3. Install backend dependencies

```bash
docker compose exec app composer install
```

### 4. Install frontend dependencies

```bash
docker compose exec frontend npm install
```

### 5. Configure environment

Create and update your `.env` file inside `backend/` with the required settings for:

- Database connection
- Sanctum
- Broadcasting / Reverb
- LiveKit
- Recording paths

### 6. Generate application key

```bash
docker compose exec app php artisan key:generate
```

### 7. Run migrations

```bash
docker compose exec app php artisan migrate
```

### 8. Create storage symlink

```bash
docker compose exec app php artisan storage:link
```

### 9. Start frontend development server

If not already running through Docker command:

```bash
docker compose exec frontend npm run dev -- --host
```

## Environment Notes

This project relies on:

- **LiveKit** for live streaming
- **Recording / egress configuration** for replay generation
- **Reverb / broadcasting configuration** for real-time events

Make sure the corresponding services are correctly configured in:

- `docker-compose.yml`
- `backend/.env`
- `backend/config/services.php`
- `backend/config/broadcasting.php`
- `backend/config/reverb.php`

## Example Features Flow

### Live Stream Flow

1. User creates a stream
2. A LiveKit room is prepared
3. Broadcaster joins the live room
4. Viewers can watch and interact in real time
5. When the stream ends, a replay video is created

### Replay Flow

1. Stream recording is generated
2. Replay metadata is saved in the database
3. Replay becomes available in the videos section
4. Related comments can be linked to the replay

## API Style

The backend follows a RESTful structure and returns JSON responses for frontend consumption.

Typical response pattern:

```json
{
  "message": "Request handled successfully.",
  "data": {}
}
```

## Current Status

The platform includes the core foundation for:

- Live streaming
- Replay videos
- Profiles
- Categories
- Reactions
- Messaging
- Notifications
- Admin features

Some parts may still be under active improvement depending on the current development stage.

## Screenshots

You can add screenshots here later for:

- Home page
- Streams page
- Stream studio
- Video replay page
- Profile page
- Messages page
- Admin dashboard

## Future Improvements

- Advanced moderation tools
- Better analytics for streams and videos
- More customizable streaming layouts
- Improved replay processing
- Enhanced recommendation and discovery system

## Author

**Bilal Baksou**  
First-year final project at **YouCode (UM6P)**

## License

This project is for educational and portfolio purposes.
