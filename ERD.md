// ==================== LOOKUPS & ROLES ====================

Table "Role" {
  "id" UUID [pk]
  "name" VARCHAR(50) [unique, not null]
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

// ==================== LOCATION HIERARCHY ====================

Table "Region" {
  "id" UUID [pk]
  "name" VARCHAR(100) [not null]
  "code" VARCHAR(50)
  "metadata" JSON
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

Table "Province" {
  "id" UUID [pk]
  "region_id" UUID [not null]
  "name" VARCHAR(100) [not null]
  "code" VARCHAR(50)
  "metadata" JSON
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

Table "City" {
  "id" UUID [pk]
  "province_id" UUID [not null]
  "name" VARCHAR(100) [not null]
  "code" VARCHAR(50)
  "metadata" JSON
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

Table "Barangay" {
  "id" UUID [pk]
  "city_id" UUID [not null]
  "name" VARCHAR(100) [not null]
  "code" VARCHAR(20)
  "metadata" JSON
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

Table "Sitio" {
  "id" UUID [pk]
  "barangay_id" UUID [not null]
  "name" VARCHAR(100) [not null]
  "metadata" JSON
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

// ==================== CORE SYSTEM ====================

Table "Address" {
  "id" UUID [pk]
  "street" VARCHAR(255)
  "purok_sitio" VARCHAR(150)
  "house_number" VARCHAR(100)
  "zip_code" VARCHAR(20)
  "full_address" VARCHAR(500)
  "barangay_id" UUID
  "barangay_name" VARCHAR(100)
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
  "deleted_at" TIMESTAMP
}

Table "Household" {
  "id" UUID [pk]
  "household_code" VARCHAR(50) [unique, not null]
  "household_name" VARCHAR(100)
  "email" VARCHAR(150) [unique]
  "member_count" INT
  "address_id" UUID [not null]
  "contact_number" VARCHAR(50)
  "emergency_contact" VARCHAR(50)
  "created_by" UUID [not null]
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
  "deleted_at" TIMESTAMP
}

Table "Member" {
  "id" UUID [pk]
  "household_id" UUID [not null]
  "name" VARCHAR(255)
  "first_name" VARCHAR(100)
  "middle_name" VARCHAR(100)
  "last_name" VARCHAR(100)
  "birth_date" DATE
  "sex" ENUM("M", "F")
  "gender" VARCHAR(20)
  "age" INT
  "relation" VARCHAR(50)
  "civil_status" VARCHAR(50)
  "education_level" VARCHAR(100)
  "occupation" VARCHAR(100)
  "is_pwd" BOOLEAN
  "is_pregnant" BOOLEAN
  "special_needs" VARCHAR(50)
  "is_graduate" BOOLEAN
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
  "deleted_at" TIMESTAMP
}

Table "User" {
  "id" UUID [pk]
  "name" VARCHAR(255) [not null]
  "username" VARCHAR(100) [unique]
  "email" VARCHAR(255) [unique, not null]
  "password" VARCHAR(255) [not null]
  "contact_number" VARCHAR(50)
  "is_active" BOOLEAN
  "role_id" UUID [not null]
  "household_id" UUID [unique]
  "must_change_password" BOOLEAN
  "temp_password" VARCHAR(255)
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

Table "Analytic" {
  "id" UUID [pk]
  "barangay_id" UUID
  "purok_sitio" VARCHAR(150)
  "record_period" DATE
  "total_households" INT
  "total_population" INT
  "total_males" INT
  "total_females" INT
  "total_pwd" INT
  "total_seniors" INT
  "total_children" INT
  "total_adults" INT
  "total_pregnant" INT
  "total_evacuees" INT
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

// ==================== DATA IMPORT ====================

Table "DataSource" {
  "id" UUID [pk]
  "type" VARCHAR(20) [not null]
  "uploaded_by" UUID [not null]
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

Table "CsvUpload" {
  "id" UUID [pk]
  "data_source_id" UUID [not null]
  "file_name" VARCHAR(255)
  "total_records" INT
  "successful_records" INT
  "failed_records" INT
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

Table "ImportLog" {
  "id" UUID [pk]
  "data_source_id" UUID [not null]
  "row_number" INT
  "status" VARCHAR(20)
  "error_message" TEXT
  "created_at" TIMESTAMP
  "updated_at" TIMESTAMP
}

// ==================== RELATIONSHIPS ====================

Ref: "Province"."region_id" > "Region"."id"
Ref: "City"."province_id" > "Province"."id"
Ref: "Barangay"."city_id" > "City"."id"
Ref: "Sitio"."barangay_id" > "Barangay"."id"

Ref: "Address"."barangay_id" > "Barangay"."id"
Ref: "Household"."address_id" > "Address"."id"
Ref: "Household"."created_by" > "User"."id"
Ref: "Member"."household_id" > "Household"."id"

Ref: "User"."role_id" > "Role"."id"
Ref: "User"."household_id" > "Household"."id"

Ref: "Analytic"."barangay_id" > "Barangay"."id"

Ref: "CsvUpload"."data_source_id" > "DataSource"."id"
Ref: "ImportLog"."data_source_id" > "DataSource"."id"
Ref: "DataSource"."uploaded_by" > "User"."id"
