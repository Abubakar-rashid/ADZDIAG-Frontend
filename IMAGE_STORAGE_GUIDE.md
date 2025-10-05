# 📸 Vehicle Image Storage - Complete Guide

## 🗂️ Storage Location

### Physical Location on Server

```
E:\php\project php\project php\vehicleimages\
```

### Web URL

```
https://www.app.adzdiag.co.uk/vehicleimages/
```

---

## ✅ Current Images (Already Present)

You already have **28 brand logos** stored:

| Image File     | Size   | Use                   |
| -------------- | ------ | --------------------- |
| abarth.png     | 1.4 MB | Abarth brand logo     |
| alfa romeo.png | 1.5 MB | Alfa Romeo brand logo |
| bmw.png        | 1.5 MB | BMW brand logo        |
| chevrolet.png  | 1.4 MB | Chevrolet brand logo  |
| chrysler.png   | 1.3 MB | Chrysler brand logo   |
| citroen.png    | 1.4 MB | Citroen brand logo    |
| dacia.png      | 1.5 MB | Dacia brand logo      |
| dodge.png      | 1.5 MB | Dodge brand logo      |
| fiat.png       | 1.4 MB | Fiat brand logo       |
| ford.png       | 1.4 MB | Ford brand logo       |
| holden.png     | 1.3 MB | Holden brand logo     |
| hyundai.png    | 1.3 MB | Hyundai brand logo    |
| jaguar.png     | 1.4 MB | Jaguar brand logo     |
| jeep.png       | 1.4 MB | Jeep brand logo       |
| kia.png        | 1.4 MB | Kia brand logo        |
| lancia.png     | 1.4 MB | Lancia brand logo     |
| land rover.png | 1.3 MB | Land Rover brand logo |
| maserati.png   | 1.4 MB | Maserati brand logo   |
| mazda.png      | 1.3 MB | Mazda brand logo      |
| mercedes.png   | 1.3 MB | Mercedes brand logo   |
| mg.png         | 846 KB | MG brand logo         |
| nissan.png     | 1.4 MB | Nissan brand logo     |
| peugeot.png    | 1.3 MB | Peugeot brand logo    |
| renault.png    | 1.3 MB | Renault brand logo    |
| smart.png      | 1.3 MB | Smart brand logo      |
| suzuki.png     | 1.2 MB | Suzuki brand logo     |
| toyota.png     | 1.3 MB | Toyota brand logo     |
| vauxhall.png   | 1.3 MB | Vauxhall brand logo   |

**Total:** 28 images, ~37 MB

---

## 🔄 How Image Storage Works

### When Adding a Brand

```
User uploads logo in admin panel
        ↓
File uploaded via HTML form
        ↓
PHP receives file in $_FILES['brand_logo']
        ↓
File saved to: vehicleimages/brandname.png
        ↓
Database stores filename: "brandname.png"
        ↓
Complete!
```

**Example:**

- You add brand "Audi"
- Upload file: `my_audi_logo.png`
- Saved as: `vehicleimages/audi.png`
- Database: `logo_filename = 'audi.png'`

### When Adding a Model

```
User uploads model image in admin panel
        ↓
File uploaded via HTML form
        ↓
PHP receives file in $_FILES['model_image']
        ↓
File saved to: vehicleimages/original_filename.png
        ↓
Database stores filename: "original_filename.png"
        ↓
Complete!
```

**Example:**

- You add model "Ford Focus"
- Upload file: `ford_focus_2010.png`
- Saved as: `vehicleimages/ford_focus_2010.png`
- Database: `image_filename = 'ford_focus_2010.png'`

---

## 📝 Database vs File System

### In Database Tables

**vehicle_brands table:**

```sql
brand_id | brand_name | logo_filename  | created_at
---------|------------|----------------|------------
1        | ford       | ford.png       | 2025-10-05
2        | bmw        | bmw.png        | 2025-10-05
```

**vehicle_models table:**

```sql
model_id | brand_id | model_name      | image_filename      | created_at
---------|----------|-----------------|---------------------|------------
1        | 1        | Focus 2005-2010 | ford.png            | 2025-10-05
2        | 1        | Transit 2014    | ford_transit.png    | 2025-10-05
```

### On File System

```
vehicleimages/
├── ford.png              ← Used by brand AND model
├── bmw.png               ← Used by brand AND model
├── ford_transit.png      ← Used only by Transit model
└── ford_focus.png        ← Used only by Focus model
```

**Note:** Models can use the same image as their brand, or have unique images!

---

## 🎯 Image Display URLs

### Brand Logos (in Program Keys grid)

```html
<img src="/vehicleimages/ford.png" /> <img src="/vehicleimages/bmw.png" />
```

### Model Images (in model selection)

```html
<img src="/vehicleimages/ford.png" /> ← Reusing brand logo
<img src="/vehicleimages/ford_focus.png" /> ← Unique model image
```

### Full URLs

```
https://www.app.adzdiag.co.uk/vehicleimages/ford.png
https://www.app.adzdiag.co.uk/vehicleimages/bmw.png
```

---

## 📤 Upload Process Details

### Code in vehicle_management.php

```php
// Upload location
$target_dir = "vehicleimages/";

// For brands: saved as brandname.png
$filename = $brand_name . '.png';
$target_file = $target_dir . $filename;

// For models: keeps original filename
$filename = basename($_FILES['model_image']['name']);
$target_file = $target_dir . $filename;

// Move uploaded file
move_uploaded_file($_FILES['file']['tmp_name'], $target_file);

// Store filename in database
$stmt->execute([$filename]);
```

---

## 🔒 Upload Restrictions

✅ **Allowed:**

- PNG files only
- Max size: 5 MB
- Any reasonable dimensions

❌ **Blocked:**

- JPG, JPEG, GIF, WebP
- Files over 5 MB
- Non-image files

---

## 💡 Best Practices

### Naming Convention

**Brand Logos:**

```
✅ ford.png
✅ bmw.png
✅ land rover.png
❌ Ford.PNG
❌ ford-logo.png
```

**Model Images:**

```
✅ ford_focus_2010.png
✅ bmw_e90_cas3.png
✅ mercedes_w204.png
❌ My Cool Car.png (spaces)
❌ car#123.png (special chars)
```

### Image Optimization

**Before Upload:**

1. Resize to reasonable size (500x500 px recommended)
2. Compress with tools like TinyPNG
3. Ensure transparent background (optional but nice)
4. Keep under 1 MB if possible

**Recommended Tools:**

- https://tinypng.com/ (compression)
- GIMP or Photoshop (resize/edit)
- Remove.bg (background removal)

---

## 🔧 Managing Images

### View All Images

```powershell
# In PowerShell
Get-ChildItem -Path "vehicleimages" -Filter "*.png"
```

### Check Storage Used

```powershell
# Total size
(Get-ChildItem -Path "vehicleimages" -File | Measure-Object -Property Length -Sum).Sum / 1MB
```

### Backup Images

```powershell
# Create backup
Copy-Item -Path "vehicleimages" -Destination "vehicleimages_backup" -Recurse
```

### Delete Unused Images

1. Check database for used filenames
2. Compare with files in folder
3. Delete files not in database

---

## 🗑️ What Happens When You Delete

### Delete a Brand

- ✅ Database record deleted
- ❌ Image file NOT automatically deleted
- 💡 Manual cleanup needed if desired

### Delete a Model

- ✅ Database record deleted
- ❌ Image file NOT automatically deleted
- 💡 Manual cleanup needed if desired

**Why?**

- Safety: Prevents accidental file loss
- Flexibility: Can reuse images
- Performance: Database operations are faster

---

## 📊 Storage Capacity

### Current Usage

```
28 images × ~1.4 MB = ~37 MB
```

### Estimated Growth

```
100 brands × 1 MB = 100 MB
500 models × 1 MB = 500 MB
Total estimate: ~600 MB for full system
```

### Server Storage

Most hosting plans provide several GB of storage, so you're fine!

---

## 🚨 Troubleshooting

### Images Not Displaying

**Problem:** Images show broken icon  
**Solution:**

1. Check file exists: `ls vehicleimages/ford.png`
2. Check filename matches database
3. Check file permissions (should be readable)
4. Check web server can access folder

### Upload Fails

**Problem:** Cannot upload image  
**Solution:**

1. Check folder permissions (needs write access)
2. Verify file is PNG format
3. Check file size is under 5 MB
4. Check disk space on server

### Wrong Image Shows

**Problem:** Wrong logo appears  
**Solution:**

1. Check database has correct filename
2. Verify file exists with exact name
3. Clear browser cache (Ctrl + F5)

---

## 📍 Quick Reference

| What                 | Where                                           |
| -------------------- | ----------------------------------------------- |
| **Physical Path**    | `E:\php\project php\project php\vehicleimages\` |
| **Web URL**          | `https://www.app.adzdiag.co.uk/vehicleimages/`  |
| **Upload Location**  | `vehicleimages/` (relative)                     |
| **Database Storage** | Filename only (e.g., `ford.png`)                |
| **File Format**      | PNG only                                        |
| **Max Size**         | 5 MB                                            |
| **Current Images**   | 28 brand logos                                  |
| **Total Size**       | ~37 MB                                          |

---

## ✅ Summary

- ✅ All images stored in `vehicleimages/` folder
- ✅ Database stores filenames, not actual images
- ✅ 28 brand logos already present
- ✅ Upload new images via admin panel
- ✅ Images persist permanently
- ✅ Web server serves images directly
- ✅ Fast and efficient setup

---

**You're all set!** Your image storage is configured and ready to use. 🎉
