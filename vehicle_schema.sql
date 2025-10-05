-- Vehicle Management Schema for PostgreSQL
-- Run this to create the necessary tables for vehicle brands and models

-- Table for vehicle brands
CREATE TABLE IF NOT EXISTS vehicle_brands (
    brand_id SERIAL PRIMARY KEY,
    brand_name VARCHAR(100) UNIQUE NOT NULL,
    logo_filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for vehicle models
CREATE TABLE IF NOT EXISTS vehicle_models (
    model_id SERIAL PRIMARY KEY,
    brand_id INTEGER NOT NULL REFERENCES vehicle_brands(brand_id) ON DELETE CASCADE,
    model_name VARCHAR(255) NOT NULL,
    model_type VARCHAR(100) NOT NULL, -- e.g., "BLADED", "Module", etc.
    procedure_name VARCHAR(255) NOT NULL, -- e.g., "focusmk2_bladed"
    image_filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(brand_id, model_name)
);

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS idx_models_brand ON vehicle_models(brand_id);

-- Insert existing brands from your images
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

-- Insert existing models from programkeys.php
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

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'E90 CAS3',
    'Module',
    'bmw_cas3',
    'bmw.png'
FROM vehicle_brands b WHERE b.brand_name = 'bmw'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Corolla 2008–2012',
    'Bladed',
    'toyota_corolla',
    'toyota.png'
FROM vehicle_brands b WHERE b.brand_name = 'toyota'
ON CONFLICT (brand_id, model_name) DO NOTHING;
