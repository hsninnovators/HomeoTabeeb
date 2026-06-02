USE homeo_tabeeb;
-- First super admin: change this password immediately after login.
-- Login: admin@homeotabeeb.local / ChangeMe123!
-- Hash generated with PHP password_hash('ChangeMe123!', PASSWORD_DEFAULT).
INSERT INTO users (name,email,phone,password_hash,role,status,created_at) VALUES
('First Super Admin','admin@homeotabeeb.local','', '$2y$12$8EIXqO745fRBsYciLKsY9uOuwRVn3btzH064TT8gmNFZIBztnx1/G','super_admin','active',NOW())
ON DUPLICATE KEY UPDATE role='super_admin';

INSERT INTO safety_rules (rule_key,trigger_keywords,severity,instruction,status,created_at) VALUES
('chest_pain','chest pain,heart pain,pressure in chest','emergency','Stop homeopathic recommendation and advise urgent medical care immediately.','active',NOW()),
('severe_breathing','severe breathing difficulty,shortness of breath cannot speak,blue lips','emergency','Seek emergency care immediately.','active',NOW()),
('stroke_signs','stroke signs,face drooping,arm weakness,speech difficulty','emergency','Call emergency services immediately.','active',NOW()),
('unconsciousness','unconscious,fainted not waking,loss of consciousness','emergency','Seek urgent emergency care.','active',NOW()),
('severe_dehydration','severe dehydration,no urination,very dry mouth,sunken eyes','emergency','Urgent medical assessment is required.','active',NOW()),
('pregnancy_emergency','pregnancy bleeding,severe pregnancy pain,no fetal movement','emergency','Pregnancy emergency requires urgent medical care.','active',NOW()),
('allergic_reaction','severe allergic reaction,swollen tongue,anaphylaxis','emergency','Seek emergency care immediately.','active',NOW()),
('suicidal_thoughts','suicidal thoughts,want to die,self harm','emergency','Contact emergency mental health support immediately.','active',NOW()),
('infant_serious','infant not feeding,baby lethargic,baby blue,severe infant fever','emergency','Serious infant symptoms need urgent medical care.','active',NOW()),
('severe_bleeding','severe bleeding,blood loss,vomiting blood','emergency','Seek urgent medical care immediately.','active',NOW()),
('fever_confusion','high fever with confusion,fever delirium,stiff neck fever','emergency','Urgent medical care is required.','active',NOW()),
('seizure_poisoning','seizure,convulsion,poisoning,swallowed poison','emergency','Emergency evaluation is required immediately.','active',NOW())
ON DUPLICATE KEY UPDATE status='active';

INSERT INTO system_settings (setting_key,setting_value,created_at) VALUES
('site_name','Homeo Tabeeb',NOW()),('site_url','https://doctor.hsninnovators.com',NOW()),('payment_method','Cash on Delivery',NOW()),('order_statuses','requested,pending_doctor_verification,approved,contacted,sent_to_vendor,out_for_delivery,delivered,cancelled,rejected',NOW()),('roles','super_admin,admin,manager,doctor',NOW()),('whatsapp_manual_only','1',NOW())
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

INSERT INTO vendors (name,phone,whatsapp,address,status,created_at) VALUES ('Default COD Vendor','','','','active',NOW()) ON DUPLICATE KEY UPDATE status='active';
