-- Personal Contact Manager — database schema
--
-- This file is the source of truth for the database. Anyone should be able to
-- rebuild the DB from scratch with:
--   mysql -u ApiUser -p personal_contacts_database < sql/schema.sql
--
-- Two tables: Users and Contacts, one-to-many.
-- Contacts.UserID -> Users.UserID is what enforces "no shared contacts".

-- TODO: CREATE TABLE Users ( ... );

-- TODO: CREATE TABLE Contacts ( ... );
