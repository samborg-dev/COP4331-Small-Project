-- Personal Contact Manager — database schema
--
-- This file is the source of truth for the database. Anyone should be able to
-- rebuild the DB from scratch with:
--   mysql -u ApiUser -p personal_contacts_database < sql/schema.sql
--
-- Two tables: Users and Contacts, one-to-many.
-- Contacts.UserID -> Users.UserID is what enforces "no shared contacts".
--
-- WARNING: this drops both tables first, so re-running it wipes all data.

DROP TABLE IF EXISTS `Contacts`;
DROP TABLE IF EXISTS `Users`;

CREATE TABLE `Users` (
  `PasswordHash` varchar(150) NOT NULL,
  `UserID` int NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `MiddleName` varchar(50) DEFAULT NULL,
  `UserName` varchar(50) NOT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Phone` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `UserName_Unique` (`UserName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Contacts` (
  `FirstName` varchar(50) NOT NULL,
  `MiddleName` varchar(50) DEFAULT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `Phone` varchar(50) DEFAULT NULL,
  `DateCreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `DateUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ContactID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  PRIMARY KEY (`ContactID`),
  KEY `UserID_Index` (`UserID`),
  CONSTRAINT `fk_UserID` FOREIGN KEY (`UserID`) REFERENCES `Users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
