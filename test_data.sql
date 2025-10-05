-- Test Data for Vehicle Management System
-- This file contains sample data to test the system
-- Run this AFTER running vehicle_schema.sql

-- Note: This assumes brands are already created from vehicle_schema.sql

-- Add more Ford models for testing
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Mondeo 2007-2014',
    'BLADED',
    'ford_mondeo_bladed',
    'ford.png'
FROM vehicle_brands b WHERE b.brand_name = 'ford'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Transit 2014-2019',
    'Module',
    'ford_transit_module',
    'ford.png'
FROM vehicle_brands b WHERE b.brand_name = 'ford'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Add BMW models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'E60 CAS3',
    'Module',
    'bmw_e60_cas3',
    'bmw.png'
FROM vehicle_brands b WHERE b.brand_name = 'bmw'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'F10 CAS4',
    'Module',
    'bmw_f10_cas4',
    'bmw.png'
FROM vehicle_brands b WHERE b.brand_name = 'bmw'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Add Mercedes models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'W204 C-Class',
    'Module',
    'mercedes_w204_module',
    'mercedes.png'
FROM vehicle_brands b WHERE b.brand_name = 'mercedes'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'W211 E-Class',
    'Module',
    'mercedes_w211_module',
    'mercedes.png'
FROM vehicle_brands b WHERE b.brand_name = 'mercedes'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Add Vauxhall models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Astra J 2010-2015',
    'BLADED',
    'vauxhall_astra_bladed',
    'vauxhall.png'
FROM vehicle_brands b WHERE b.brand_name = 'vauxhall'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Insignia 2008-2013',
    'BLADED',
    'vauxhall_insignia_bladed',
    'vauxhall.png'
FROM vehicle_brands b WHERE b.brand_name = 'vauxhall'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Add Renault models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Clio III 2005-2012',
    'Card',
    'renault_clio3_card',
    'renault.png'
FROM vehicle_brands b WHERE b.brand_name = 'renault'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Megane II 2002-2008',
    'Card',
    'renault_megane2_card',
    'renault.png'
FROM vehicle_brands b WHERE b.brand_name = 'renault'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Add Peugeot models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    '308 2007-2013',
    'BLADED',
    'peugeot_308_bladed',
    'peugeot.png'
FROM vehicle_brands b WHERE b.brand_name = 'peugeot'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    '207 2006-2012',
    'BLADED',
    'peugeot_207_bladed',
    'peugeot.png'
FROM vehicle_brands b WHERE b.brand_name = 'peugeot'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Add Citroen models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'C4 2004-2010',
    'BLADED',
    'citroen_c4_bladed',
    'citroen.png'
FROM vehicle_brands b WHERE b.brand_name = 'citroen'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'C5 2008-2017',
    'BLADED',
    'citroen_c5_bladed',
    'citroen.png'
FROM vehicle_brands b WHERE b.brand_name = 'citroen'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Add Nissan models
INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Qashqai 2007-2013',
    'BLADED',
    'nissan_qashqai_bladed',
    'nissan.png'
FROM vehicle_brands b WHERE b.brand_name = 'nissan'
ON CONFLICT (brand_id, model_name) DO NOTHING;

INSERT INTO vehicle_models (brand_id, model_name, model_type, procedure_name, image_filename)
SELECT 
    b.brand_id,
    'Micra K12 2003-2010',
    'BLADED',
    'nissan_micra_bladed',
    'nissan.png'
FROM vehicle_brands b WHERE b.brand_name = 'nissan'
ON CONFLICT (brand_id, model_name) DO NOTHING;

-- Verify data
SELECT 
    'Total Brands:' as info,
    COUNT(*) as count
FROM vehicle_brands

UNION ALL

SELECT 
    'Total Models:' as info,
    COUNT(*) as count
FROM vehicle_models;

-- Show brands with model count
SELECT 
    b.brand_name,
    COUNT(m.model_id) as model_count,
    b.logo_filename
FROM vehicle_brands b
LEFT JOIN vehicle_models m ON b.brand_id = m.brand_id
GROUP BY b.brand_id, b.brand_name, b.logo_filename
ORDER BY model_count DESC, b.brand_name;

-- Show all models
SELECT 
    b.brand_name,
    m.model_name,
    m.model_type,
    m.procedure_name,
    m.image_filename
FROM vehicle_models m
JOIN vehicle_brands b ON m.brand_id = b.brand_id
ORDER BY b.brand_name, m.model_name;
