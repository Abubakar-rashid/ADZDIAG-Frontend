-- ============================================
-- VEHICLE MANAGEMENT SYSTEM - SETUP SCRIPT
-- Copy and paste this entire script into pgAdmin Query Tool
-- Then click Execute (F5) or the Play button
-- ============================================

-- Step 1: Create vehicle_brands table
CREATE TABLE IF NOT EXISTS vehicle_brands (
    brand_id SERIAL PRIMARY KEY,
    brand_name VARCHAR(100) UNIQUE NOT NULL,
    logo_filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 2: Create vehicle_models table
CREATE TABLE IF NOT EXISTS vehicle_models (
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

-- Step 3: Create index for performance
CREATE INDEX IF NOT EXISTS idx_models_brand ON vehicle_models(brand_id);

-- Step 4: Insert all 28 vehicle brands
INSERT INTO vehicle_brands (brand_name, logo_filename) VALUES
    ('abarth', 'abarth.png'),
    ('alfa romeo', 'alfa romeo.png'),
    ('bmw', 'bmw.png'),
    ('chevrolet', 'chevrolet.png'),
    ('chrysler', 'chrysler.png'),
    ('citroen', 'citroen.png'),
    ('dacia', 'dacia.png'),
    ('dodge', 'dodge.png'),
    ('fiat', 'fiat.png'),
    ('ford', 'ford.png'),
    ('holden', 'holden.png'),
    ('hyundai', 'hyundai.png'),
    ('jaguar', 'jaguar.png'),
    ('jeep', 'jeep.png'),
    ('kia', 'kia.png'),
    ('lancia', 'lancia.png'),
    ('land rover', 'land rover.png'),
    ('maserati', 'maserati.png'),
    ('mazda', 'mazda.png'),
    ('mercedes', 'mercedes.png'),
    ('mg', 'mg.png'),
    ('nissan', 'nissan.png'),
    ('peugeot', 'peugeot.png'),
    ('renault', 'renault.png'),
    ('smart', 'smart.png'),
    ('suzuki', 'suzuki.png'),
    ('toyota', 'toyota.png'),
    ('vauxhall', 'vauxhall.png')
ON CONFLICT (brand_name) DO NOTHING;

-- Step 5: Insert example Ford models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Focus 2005–2010',
    'BLADED',
    'focusmk2_bladed',
    'ford.png'
FROM vehicle_brands b WHERE b.brand_name = 'ford'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Fiesta 2002–2008',
    'BLADED',
    'fiesta_bladed',
    'ford.png'
FROM vehicle_brands b WHERE b.brand_name = 'ford'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Step 6: Insert example BMW model
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'E90 CAS3',
    'Module',
    'bmw_cas3',
    'bmw.png'
FROM vehicle_brands b WHERE b.brand_name = 'bmw'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Step 7: Insert example Toyota model
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Corolla 2008–2012',
    'Bladed',
    'toyota_corolla',
    'toyota.png'
FROM vehicle_brands b WHERE b.brand_name = 'toyota'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- ============================================
-- VERIFICATION QUERIES
-- Run these to verify everything worked
-- ============================================

-- Check total brands (should be 28)
SELECT 'Total Brands:' as info, COUNT(*) as count FROM vehicle_brands;

-- Check total models (should be 4)
SELECT 'Total Models:' as info, COUNT(*) as count FROM vehicle_models;

-- Show all brands
SELECT brand_id, brand_name, logo_filename, created_at 
FROM vehicle_brands 
ORDER BY brand_name;

-- Show all models with brand names
SELECT 
    b.brand_name,
    m.model_name,
    m.model_type,
    m.procedure_name,
    m.image_filename
FROM vehicle_models m
JOIN vehicle_brands b ON m.brand_id = b.brand_id
ORDER BY b.brand_name, m.model_name;

-- ============================================
-- SUCCESS!
-- If you see no errors, your setup is complete!
-- 
-- Next steps:
-- 1. Go to: https://www.app.adzdiag.co.uk/vehicle_management.php
-- 2. Log in as admin
-- 3. Start adding more vehicles!
-- ============================================
