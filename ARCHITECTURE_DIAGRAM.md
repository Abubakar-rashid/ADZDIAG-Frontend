# Vehicle Management System - Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    ADZDIAG VEHICLE SYSTEM                    │
└─────────────────────────────────────────────────────────────┘

┌────────────────────────┐         ┌────────────────────────┐
│   ADMIN INTERFACE      │         │   USER INTERFACE       │
│  (vehicle_management)  │         │   (programkeys.php)    │
└────────────────────────┘         └────────────────────────┘
         │                                    │
         │ Add/Delete                         │ View/Select
         │ Brands/Models                      │ Vehicles
         ▼                                    ▼
┌─────────────────────────────────────────────────────────────┐
│                     API LAYER (api_vehicles.php)            │
│  - Fetches brands and models                                │
│  - Returns JSON data                                        │
│  - Session validation                                       │
└─────────────────────────────────────────────────────────────┘
                           │
                           │ SQL Queries
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                  DATABASE (PostgreSQL)                      │
│  ┌──────────────────┐       ┌─────────────────┐           │
│  │ vehicle_brands   │       │ vehicle_models  │           │
│  ├──────────────────┤       ├─────────────────┤           │
│  │ brand_id (PK)    │←──────│ brand_id (FK)   │           │
│  │ brand_name       │       │ model_id (PK)   │           │
│  │ logo_filename    │       │ model_name      │           │
│  │ created_at       │       │ model_type      │           │
│  │ updated_at       │       │ procedure_name  │           │
│  └──────────────────┘       │ image_filename  │           │
│                             │ created_at      │           │
│                             │ updated_at      │           │
│                             └─────────────────┘           │
└─────────────────────────────────────────────────────────────┘
                           │
                           │ File Storage
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              FILE SYSTEM (vehicleimages/)                   │
│  - ford.png (brand logo)                                    │
│  - bmw.png (brand logo)                                     │
│  - ford_focus.png (model image)                            │
│  - bmw_e90.png (model image)                               │
│  - ... all vehicle images ...                              │
└─────────────────────────────────────────────────────────────┘
```

## Data Flow Diagrams

### 1. Adding a New Brand

```
┌─────────────┐
│   ADMIN     │
└──────┬──────┘
       │ 1. Fill form (name + logo)
       ▼
┌─────────────────────────────┐
│ vehicle_management.php      │
│ - Validate input            │
│ - Upload image              │
│ - Insert to database        │
└──────┬──────────────────────┘
       │ 2. Save logo to disk
       ▼
┌─────────────────────────────┐
│ vehicleimages/              │
│ └── brandname.png           │
└──────┬──────────────────────┘
       │ 3. Insert brand data
       ▼
┌─────────────────────────────┐
│ Database                    │
│ vehicle_brands table        │
│ + New brand record          │
└──────┬──────────────────────┘
       │ 4. Success message
       ▼
┌─────────────────────────────┐
│ Admin sees confirmation     │
└─────────────────────────────┘
```

### 2. Adding a New Model

```
┌─────────────┐
│   ADMIN     │
└──────┬──────┘
       │ 1. Select brand + fill model details
       ▼
┌─────────────────────────────┐
│ vehicle_management.php      │
│ - Validate input            │
│ - Upload model image        │
│ - Link to brand_id          │
└──────┬──────────────────────┘
       │ 2. Save image to disk
       ▼
┌─────────────────────────────┐
│ vehicleimages/              │
│ └── model_image.png         │
└──────┬──────────────────────┘
       │ 3. Insert model data
       ▼
┌─────────────────────────────┐
│ Database                    │
│ vehicle_models table        │
│ + New model record          │
│ (linked to brand_id)        │
└──────┬──────────────────────┘
       │ 4. Success message
       ▼
┌─────────────────────────────┐
│ Admin sees confirmation     │
└─────────────────────────────┘
```

### 3. User Viewing Vehicles

```
┌─────────────┐
│    USER     │
└──────┬──────┘
       │ 1. Opens Program Keys
       ▼
┌─────────────────────────────┐
│ programkeys.php             │
│ - Loads page                │
│ - Calls API via JavaScript  │
└──────┬──────────────────────┘
       │ 2. Fetch request
       ▼
┌─────────────────────────────┐
│ api_vehicles.php            │
│ - Check session             │
│ - Query database            │
│ - Format JSON response      │
└──────┬──────────────────────┘
       │ 3. SQL SELECT
       ▼
┌─────────────────────────────┐
│ Database                    │
│ - Get all brands            │
│ - Get all models            │
│ - Join tables               │
└──────┬──────────────────────┘
       │ 4. Return JSON
       ▼
┌─────────────────────────────┐
│ programkeys.php             │
│ - Receive JSON data         │
│ - Build brand grid          │
│ - Build model grids         │
└──────┬──────────────────────┘
       │ 5. Display vehicles
       ▼
┌─────────────────────────────┐
│ User sees vehicle grid      │
│ - Click brand → see models  │
│ - Click model → connect     │
└─────────────────────────────┘
```

## File Dependencies

```
vehicle_management.php
├── config.php (database connection)
├── PHPMailer (composer autoload)
└── vehicleimages/ (file uploads)

api_vehicles.php
├── config.php (database connection)
└── session validation

programkeys.php
├── api_vehicles.php (AJAX calls)
└── vehicleimages/ (image display)

Database Tables
├── vehicle_brands (parent table)
└── vehicle_models (child table with FK)
```

## Security Layers

```
┌─────────────────────────────────────────────────────┐
│ Layer 1: Authentication                             │
│ - Session check: $_SESSION['logged_in']             │
│ - User must be logged in                            │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Layer 2: Authorization (Admin Only)                 │
│ - Role check: $_SESSION['role'] == 'admin'          │
│ - Only admins can access vehicle_management.php     │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Layer 3: Input Validation                           │
│ - Sanitize all user inputs                          │
│ - Validate file types (PNG only)                    │
│ - Check file sizes (max 5MB)                        │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Layer 4: SQL Injection Prevention                   │
│ - Use prepared statements (PDO)                     │
│ - Parameter binding for all queries                 │
│ - No direct SQL concatenation                       │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────┐
│ Layer 5: File Upload Security                       │
│ - Verify image type with getimagesize()             │
│ - Restrict to PNG format only                       │
│ - Save with controlled filenames                    │
│ - Store in designated directory only                │
└─────────────────────────────────────────────────────┘
```

## Database Relationships

```
vehicle_brands (1) ──────── (Many) vehicle_models
     │                              │
     │ CASCADE DELETE              │
     │                              │
     └──────────────────────────────┘

When a brand is deleted:
- All associated models are automatically deleted
- Database ensures referential integrity
- ON DELETE CASCADE constraint handles cleanup
```

## User Workflows

### Admin Workflow

```
1. Login → Admin Panel
2. Click "Vehicle Management"
3. Add Brand:
   - Enter brand name
   - Upload logo
   - Submit
4. Add Model:
   - Select brand
   - Enter details
   - Upload image
   - Submit
5. View Tables:
   - See all brands
   - See all models
6. Delete (if needed):
   - Delete model (single)
   - Delete brand (+ all models)
```

### User Workflow

```
1. Login → Dashboard
2. Click "Program Keys"
3. See brand grid (loaded from database)
4. Click brand → See models
5. Click model → Connection interface
6. Connect to vehicle
7. Perform operations (Add Key, Delete Keys, etc.)
```

## Technology Stack

```
Frontend:
├── HTML5
├── CSS3 (Tabler Framework)
├── JavaScript (ES6+)
│   ├── Fetch API
│   ├── Web Serial API
│   └── Async/Await
└── Bootstrap Icons

Backend:
├── PHP 7.4+
├── PostgreSQL 12+
├── PDO (Database Abstraction)
└── PHPMailer (Email)

File System:
├── vehicleimages/ (image storage)
└── PNG format images

Security:
├── Session Management
├── Prepared Statements
├── Input Validation
└── File Upload Filtering
```

## API Endpoints

```
GET  /api_vehicles.php
├── Authentication: Session required
├── Returns: JSON
├── Content:
│   ├── brands[] (all brands)
│   └── models{} (grouped by brand)
└── Usage: AJAX call from programkeys.php

POST /vehicle_management.php
├── Authentication: Admin session required
├── Actions:
│   ├── add_brand (brand name + logo file)
│   ├── add_model (brand_id + model details + image)
│   ├── delete_brand (brand_id)
│   └── delete_model (model_id)
└── Returns: HTML page with success/error message
```

## Performance Considerations

```
Database Queries:
├── Indexed on brand_id for fast lookups
├── JOIN queries optimized
└── Minimal queries per page load

File Storage:
├── Images served directly by web server
├── No PHP processing for image display
└── Browser caching enabled

JavaScript:
├── Data fetched once on page load
├── DOM manipulation minimized
└── Event delegation where possible

Caching:
├── Browser caches images
├── Session data cached
└── Database connection pooling
```

## Scalability

```
Current Capacity:
├── Brands: Unlimited (practical limit ~1000)
├── Models per brand: Unlimited (practical limit ~100)
├── Image storage: Limited by disk space
└── Concurrent users: Limited by server resources

Growth Path:
├── Add pagination for large datasets
├── Implement image CDN
├── Add caching layer (Redis)
└── Optimize database indexes
```

---

**Architecture Version**: 1.0  
**Last Updated**: October 4, 2025  
**Status**: Production Ready ✅
