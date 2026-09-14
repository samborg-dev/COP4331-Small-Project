-- Test data. Run after schema.sql.
--
-- Needs 2 users and ~15 contacts split between them. Two users is what lets you
-- prove contacts are private; 15 rows is what makes partial-match search
-- visibly do something during the demo.
--
-- Passwords must be stored as password_hash() output, not plaintext. Generate
-- one with:  php -r "echo password_hash('test123', PASSWORD_DEFAULT);"

-- TODO: INSERT INTO Users ...

-- TODO: INSERT INTO Contacts ...
