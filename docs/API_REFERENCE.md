# PhimTop1 CMS API Reference

PhimTop1 CMS provides a set of RESTful APIs to interface with the core database and features. All API endpoints are located under `/api/v1/`.

## Endpoints

### 1. Home (`/api/v1/home.php`)
- **Method:** `GET`
- **Params:** `page` (optional)
- **Description:** Returns the featured slider and latest updated movies.

### 2. Movie Detail (`/api/v1/movie.php`)
- **Method:** `GET`
- **Params:** `slug` (required)
- **Description:** Returns detailed information about a movie and its episodes.

### 3. Categories (`/api/v1/categories.php`)
- **Method:** `GET`
- **Description:** Returns a list of all genres, countries, and release years.

### 4. Category Filter (`/api/v1/category.php`)
- **Method:** `GET`
- **Params:** 
  - `type` (required): `the-loai`, `quoc-gia`, `nam-phat-hanh`, `danh-sach`
  - `slug` (required): slug of the category
  - `page` (optional)
- **Description:** Returns a list of movies within a specific category.

### 5. Search (`/api/v1/search.php`)
- **Method:** `GET`
- **Params:** `keyword` (required), `page` (optional)
- **Description:** Searches for movies by keyword.

### 6. Authentication (`/api/v1/auth.php`)
- **Method:** `POST` / `GET`
- **Params:** `action` (`login`, `register`, `logout`, `profile`)
- **Description:** Handles user registration, login, and fetching user profile.

### 7. Watch Party (`/api/v1/watch_party.php`)
- **Method:** `POST` / `GET`
- **Description:** Manage co-watching sessions (create room, join room, sync player state).

### 8. Watching Session (`/api/v1/watching_session.php`)
- **Method:** `POST`
- **Description:** Heartbeat to sync the user's progress and active device (Mobile to Web integration).

## Response Format
Most APIs return a standard JSON object:
```json
{
  "status": true,
  "data": {
    "items": []
  },
  "message": ""
}
```
