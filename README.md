# 🧠 Mini Social Feed API (Laravel 12)

This is a RESTful API built using **Laravel 12** that provides user authentication, post creation with optional media upload, reactions, and comments functionality.  
It demonstrates clean architecture, database relationships, API validation, and Laravel Sanctum authentication.

---

## 🚀 How to Run the Project

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/solankeajay/MiniSocialFeedAPI.git
cd MiniSocialFeedAPI
```

### 2️⃣ Install Dependencies
```bash
composer install

```

### 3️⃣ Set up environment file
```bash
cp .env.example .env

```

### Update .env file:

Database accsess
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=MiniSocialFeed
DB_USERNAME=root
DB_PASSWORD=

```

App Url 

```
APP_URL=http://localhost
```


### 4️⃣ Generate app key
```bash
php artisan key:generate

```

### 5️⃣ Run migrations
```bash
php artisan migrate

```

### 6️⃣ Link Storage for Media Uploads

```bash
php artisan storage:link

```

### 7️⃣ Start the Local Server
```bash
php artisan serve

```

Your API will be running at:

👉 http://127.0.0.1:8000/api


## Database Setup

Database: MiniSocialFeed

| Table                    | Description                                     |
| ------------------------ | ----------------------------------------------- |
| `users`                  | Stores registered users                         |
| `posts`                  | Stores posts created by users                   |
| `reactions`              | Stores user reactions (likes/dislikes) on posts |
| `comments`               | Stores comments on posts                        |
| `personal_access_tokens` | Stores Sanctum API tokens                       |



## Relationships

- User
  - hasMany: posts, comments, reactions
- Post
  - belongsTo: user
  - hasMany: comments, reactions
- Comment
  - belongsTo: post, user
- Reaction
  - belongsTo: post, user






## Authentication

Authentication is implemented using Laravel Sanctum.  
After a successful login you will receive a Bearer token which must be included in the Authorization header for all protected endpoints:

Example:
```
Authorization: Bearer <your_token_here>
```

Notes:
- Login (POST /api/auth/login) returns the token.
- Use POST /api/auth/logout to revoke the current token.
- Use GET /api/auth/me to retrieve the authenticated user's details.

## API Endpoints

#### 🧾 Postman collection

A Postman collection for the API is included in this project root.  
Download or import it into Postman using the file below:

- Local file: [MiniSocialFeedAPI.postman_collection.json](./MiniSocialFeedAPI.postman_collection.json)

To import in Postman:
1. Open Postman → File → Import.
2. Choose "File" and select the JSON file above.
3. Click "Import" — the collection and example requests will be available.
4. Update Collation Variable value with your baseurl like (http://127.0.0.1:8000/api OR Other).


All endpoints are prefixed with /api.

### Authentication
| Method | Endpoint     | Description                      | Auth |
| ------ | ------------ | -------------------------------- | ---- |
| POST   | /api/auth/signup  | Register a new user              | ❌   |
| POST   | /api/auth/login   | Authenticate and receive token   | ❌   |
| GET    | /api/auth/me      | Get authenticated user details   | ✅   |
| POST   | /api/auth/logout  | Revoke current token (logout)    | ✅   |

### Posts
| Method | Endpoint            | Description                                 | Auth |
| ------ | ------------------- | ------------------------------------------- | ---- |
| GET    | /api/posts          | List authenticated user's posts (paginated) | ✅   |
| GET    | /api/posts/feed     | Get feed of all users' posts                | ✅   |
| POST   | /api/posts          | Create a new post (optional media upload)   | ✅   |
| GET    | /api/posts/{id}     | View a single post by ID                    | ✅   |
| DELETE | /api/posts/{id}     | Delete a post (removes media file if exists)| ✅   |

### Comments
| Method | Endpoint                       | Description                        | Auth |
| ------ | ------------------------------ | ---------------------------------- | ---- |
| POST   | /api/posts/{post_id}/comment  | Add a comment to a post            | ✅   |
| GET    | /api/posts/{post_id}/comments  | List comments for a post           | ✅   |


### Reactions
| Method | Endpoint                   | Description                         | Auth |
| ------ | -------------------------- | ----------------------------------- | ---- |
| POST   | /api/posts/{post_id}/like  | Like a post                          | ✅   |
| POST   | /api/posts/{post_id}/dislike | Dislike a post                   | ✅   |

Authentication-required endpoints return 401 when no valid token is provided. Successful POST requests typically return the created resource; validation errors return 422 with details.



## Notes & assumptions

- Authentication: implemented with Laravel Sanctum (token-based).
- Media storage: uploaded files are stored in storage/app/public/media and exposed via php artisan storage:link.
- Tests: feature tests cover authentication flows (see tests/Feature). Run tests with:
  ```
  php artisan test
  ```
- Features: API supports pagination and basic search for posts.
- Errors: protected endpoints return 401 when unauthenticated; validation failures return 422 with error details.

## Developer / Contact

Ajay Solanki — Full Stack PHP (Laravel + Vue.js) Developer

- Email: ajay814040@gmail.com
- Mobile: 9712727186 / 8140408454
