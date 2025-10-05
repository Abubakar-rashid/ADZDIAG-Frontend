# 🚗 AdzDIAG Vehicle Management System

> Complete admin panel for managing vehicle brands and models with database-driven dynamic loading

## 📦 What's Included

This implementation provides a complete vehicle management system for your AdzDIAG application:

###  Core Features

- **Add Vehicle Brands** - Upload logos, create brand entries
- **Add Vehicle Models** - Link models to brands with custom images
- **Delete Management** - Remove brands and models (with cascade protection)
- **Dynamic Loading** - Program Keys page loads vehicles from database
- **Image Management** - Automated PNG storage in vehicleimages/ folder
- **Admin Panel** - Full CRUD interface for vehicle data
- **API Endpoint** - RESTful JSON API for vehicle data
- **Security** - Admin-only access, input validation, SQL injection protection

---

## 📁 Files Created

| File                           | Purpose                              |
| ------------------------------ | ------------------------------------ |
| `vehicle_management.php`       | Admin panel UI for managing vehicles |
| `api_vehicles.php`             | JSON API endpoint for vehicle data   |
| `vehicle_schema.sql`           | Database tables and initial data     |
| `test_data.sql`                | Sample test data (optional)          |
| `setup_database.bat`           | Windows script for easy setup        |
| `VEHICLE_MANAGEMENT_README.md` | Detailed documentation               |
| `IMPLEMENTATION_SUMMARY.md`    | Technical overview                   |
| `QUICK_START_GUIDE.md`         | Step-by-step getting started         |
| `ARCHITECTURE_DIAGRAM.md`      | System architecture diagrams         |

### Modified Files

| File              | Changes                           |
| ----------------- | --------------------------------- |
| `programkeys.php` | Now loads from database via API   |
| `admin.php`       | Added "Vehicle Management" button |

---

## 🚀 Installation

### Prerequisites

- ✅ PostgreSQL database running
- ✅ PHP 7.4+ with PDO extension
- ✅ Admin account access
- ✅ Write permissions on `vehicleimages/` folder

### Step 1: Setup Database

**Option A - Windows (Easy)**

```bash
cd "e:\php\project php\project php"
.\setup_database.bat
```

**Option B - Manual**

```bash
psql -h 217.154.59.146 -p 5432 -U admin -d mydb -f vehicle_schema.sql
```

**Option C - pgAdmin**

1. Open pgAdmin
2. Connect to your database
3. Open Query Tool
4. Load and execute `vehicle_schema.sql`

### Step 2: Verify Tables Created

```sql
-- Check tables exist
SELECT table_name
FROM information_schema.tables
WHERE table_schema = 'public'
  AND table_name IN ('vehicle_brands', 'vehicle_models');

-- Check data
SELECT COUNT(*) FROM vehicle_brands;  -- Should return 28
SELECT COUNT(*) FROM vehicle_models;  -- Should return 4
```

### Step 3: Access Admin Panel

1. Navigate to: `https://www.app.adzdiag.co.uk/vehicle_management.php`
2. Login with admin credentials
3. You should see the vehicle management interface

### Step 4: Test the System

1. **View existing data** - Check the tables at the bottom
2. **Add a test brand** - Try adding "Audi" with a logo
3. **Add a test model** - Add an Audi A4 to the Audi brand
4. **Check Program Keys** - Visit `programkeys.php` and verify the new brand appears
5. **Delete test data** - Remove your test entries

---

## 📖 Quick Usage Guide

### Adding a Brand

```
1. Go to vehicle_management.php
2. Section: "Add New Brand"
3. Brand Name: Enter lowercase name (e.g., "audi")
4. Brand Logo: Upload PNG file
5. Click "Add Brand"
6. Logo saved as: vehicleimages/audi.png
```

### Adding a Model

```
1. Go to vehicle_management.php
2. Section: "Add New Model"
3. Select Brand: Choose from dropdown
4. Model Name: "A4 2008-2012"
5. Model Type: "BLADED" (or Module, Card, etc.)
6. Procedure Name: "audi_a4_bladed"
7. Model Image: Upload PNG file
8. Click "Add Model"
```

### Adding the Procedure

After adding a model, add the procedure code to `programkeys.php`:

```javascript
// Find the procedures object around line 180
const procedures = {
  // ... existing procedures ...

  audi_a4_bladed: async (writer, reader, logs) => {
    logs.textContent += "\n[ 🚗 Initialising Audi A4... ]";

    // Initialize ELM327
    await sendCommand(writer, logs, "ATZ", 3000);
    await sendCommand(writer, logs, "ATE0", 200);
    await sendCommand(writer, logs, "ATL0", 200);

    // Your specific commands here...

    logs.textContent += "\n✅ Audi A4 ready";
  },
};
```

---

## 📊 Database Schema

### vehicle_brands

```sql
CREATE TABLE vehicle_brands (
    brand_id SERIAL PRIMARY KEY,
    brand_name VARCHAR(100) UNIQUE NOT NULL,
    logo_filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### vehicle_models

```sql
CREATE TABLE vehicle_models (
    model_id SERIAL PRIMARY KEY,
    brand_id INTEGER NOT NULL REFERENCES vehicle_brands(brand_id) ON DELETE CASCADE,
    model_name VARCHAR(255) NOT NULL,
    model_type VARCHAR(100) NOT NULL,
    procedure_name VARCHAR(255) NOT NULL,
    image_filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(brand_id, model_name)
);
```

**Key Points:**

- `brand_id` is auto-incrementing primary key
- `brand_name` must be unique
- Models link to brands via `brand_id` foreign key
- Deleting a brand cascades to delete all its models
- Unique constraint prevents duplicate model names per brand

---

## 🔒 Security Features

✅ **Authentication** - Session-based login required  
✅ **Authorization** - Admin role required for vehicle management  
✅ **SQL Injection Protection** - Prepared statements with PDO  
✅ **File Upload Validation** - PNG only, max 5MB  
✅ **Input Sanitization** - All inputs cleaned before display  
✅ **XSS Prevention** - htmlspecialchars() on all outputs  
✅ **CSRF Protection** - Session validation on all requests

---

## 🎯 API Documentation

### GET `/api_vehicles.php`

**Authentication:** Session required  
**Response Format:** JSON

```json
{
  "success": true,
  "brands": [
    {
      "brand_id": 1,
      "name": "ford",
      "logo": "/vehicleimages/ford.png"
    },
    {
      "brand_id": 2,
      "name": "bmw",
      "logo": "/vehicleimages/bmw.png"
    }
  ],
  "models": {
    "ford": [
      {
        "model_id": 1,
        "name": "Focus 2005–2010",
        "type": "BLADED",
        "procedure": "focusmk2_bladed",
        "img": "/vehicleimages/ford.png"
      }
    ],
    "bmw": [
      {
        "model_id": 3,
        "name": "E90 CAS3",
        "type": "Module",
        "procedure": "bmw_cas3",
        "img": "/vehicleimages/bmw.png"
      }
    ]
  }
}
```

**Error Response:**

```json
{
  "success": false,
  "error": "Database error: Connection failed"
}
```

---

## 🛠️ Troubleshooting

### Issue: "Cannot access vehicle_management.php"

**Solution:**

- Ensure logged in as admin user
- Check `$_SESSION['role']` is set to 'admin'
- Verify session is active

### Issue: "Database tables not found"

**Solution:**

- Run `vehicle_schema.sql`
- Check PostgreSQL connection in `config.php`
- Verify database name is 'mydb'

### Issue: "Images not displaying"

**Solution:**

- Check `vehicleimages/` directory exists
- Verify web server has read permissions
- Check image filenames in database match actual files
- Use browser dev tools to check image URLs

### Issue: "Brand already exists"

**Solution:**

- Brand names must be unique
- Check existing brands table
- Delete old entry or use different name

### Issue: "File upload failed"

**Solution:**

- Check `vehicleimages/` has write permissions
- Verify file is PNG format
- Check file size is under 5MB
- Look at PHP error logs

### Issue: "Program Keys not showing new vehicles"

**Solution:**

- Hard refresh the page (Ctrl + F5)
- Check browser console for JavaScript errors
- Verify API endpoint returns data (visit api_vehicles.php)
- Check database has the new data

---

## 📈 Performance Tips

1. **Database Indexes** - Already created on `brand_id` for fast lookups
2. **Image Optimization** - Compress PNG files before upload
3. **Browser Caching** - Images cached by browser automatically
4. **Minimal Queries** - API loads all data in one request
5. **Connection Pooling** - PDO handles connection reuse

---

## 🔄 Backup & Restore

### Backup Vehicle Data

```sql
-- Export brands
COPY vehicle_brands TO '/tmp/brands_backup.csv' CSV HEADER;

-- Export models
COPY vehicle_models TO '/tmp/models_backup.csv' CSV HEADER;
```

### Backup Images

```bash
# Windows
xcopy /E /I "vehicleimages" "vehicleimages_backup"

# Linux/Mac
cp -r vehicleimages vehicleimages_backup
```

### Restore Data

```sql
-- Import brands
COPY vehicle_brands FROM '/tmp/brands_backup.csv' CSV HEADER;

-- Import models
COPY vehicle_models FROM '/tmp/models_backup.csv' CSV HEADER;
```

---

## 📞 Support & Documentation

| Document                       | Description                       |
| ------------------------------ | --------------------------------- |
| `QUICK_START_GUIDE.md`         | Fast setup and first steps        |
| `VEHICLE_MANAGEMENT_README.md` | Detailed usage instructions       |
| `IMPLEMENTATION_SUMMARY.md`    | Technical implementation details  |
| `ARCHITECTURE_DIAGRAM.md`      | System architecture and data flow |

---

## 🎓 Example Workflows

### Scenario 1: Adding Multiple Ford Models

```
1. Ford brand already exists ✓
2. Add Ford Transit:
   - Brand: Ford
   - Model: Transit 2014-2019
   - Type: Module
   - Procedure: ford_transit_module
   - Image: ford_transit.png

3. Add Ford Ranger:
   - Brand: Ford
   - Model: Ranger 2012-2019
   - Type: BLADED
   - Procedure: ford_ranger_bladed
   - Image: ford_ranger.png

4. Add procedures to programkeys.php
5. Test in Program Keys ✓
```

### Scenario 2: Adding New Brand with Models

```
1. Add Audi brand:
   - Name: audi
   - Logo: audi.png

2. Add Audi A4:
   - Brand: Audi
   - Model: A4 2008-2012
   - Type: BLADED
   - Procedure: audi_a4_bladed
   - Image: audi_a4.png

3. Add Audi A6:
   - Brand: Audi
   - Model: A6 2005-2011
   - Type: Module
   - Procedure: audi_a6_module
   - Image: audi_a6.png

4. Add procedures to programkeys.php
5. Test in Program Keys ✓
```

---

## ✅ Checklist

### Initial Setup

- [ ] Run `setup_database.bat` or execute `vehicle_schema.sql`
- [ ] Verify 28 brands created
- [ ] Verify 4 models created
- [ ] Check `vehicleimages/` directory exists
- [ ] Test access to `vehicle_management.php`

### Testing

- [ ] Add test brand successfully
- [ ] Add test model to brand
- [ ] View in Program Keys page
- [ ] Delete test model
- [ ] Delete test brand
- [ ] Verify cascade deletion

### Production Ready

- [ ] All existing brands migrated
- [ ] All existing models migrated
- [ ] All images uploaded
- [ ] All procedures added to programkeys.php
- [ ] Testing complete
- [ ] Backup created

---

## 🌟 Features Summary

| Feature          | Status | Description                   |
| ---------------- | ------ | ----------------------------- |
| Brand Management | ✅     | Add/delete brands with logos  |
| Model Management | ✅     | Add/delete models with images |
| Dynamic Loading  | ✅     | Database-driven vehicle lists |
| Image Upload     | ✅     | PNG storage in vehicleimages/ |
| API Endpoint     | ✅     | JSON data for frontend        |
| Admin Panel      | ✅     | Full CRUD interface           |
| Security         | ✅     | Authentication & validation   |
| Documentation    | ✅     | Complete guides provided      |

---

## 📝 Version History

**Version 1.0** (October 4, 2025)

- Initial release
- Database schema created
- Admin panel implemented
- API endpoint created
- Program Keys integration
- Documentation complete

---

## 🤝 Contributing

To extend this system:

1. **Add new fields** - Modify schema and add to forms
2. **Add image types** - Update file validation
3. **Add search** - Implement filtering in admin panel
4. **Add pagination** - For large datasets
5. **Add categories** - Group models by type

---

## 📄 License

Part of AdzDIAG System © 2025 All Rights Reserved

---

**🎉 System Status: Production Ready**

For questions or issues, refer to the documentation files or check the troubleshooting section.

**Happy vehicle managing! 🚗🔧**
