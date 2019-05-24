-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2019 at 12:21 PM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wavesnew`
--

-- --------------------------------------------------------

--
-- Table structure for table `anonimity_reports`
--

CREATE TABLE `anonimity_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `schoolProfileId` int(10) UNSIGNED NOT NULL,
  `responderId` int(10) UNSIGNED NOT NULL,
  `studentId` int(10) UNSIGNED NOT NULL,
  `threadId` int(10) UNSIGNED NOT NULL,
  `message` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_requests`
--

CREATE TABLE `conversation_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `studentProfileId` int(10) UNSIGNED NOT NULL,
  `responderProfileId` int(10) UNSIGNED NOT NULL,
  `urgencyLevel` tinyint(3) UNSIGNED NOT NULL,
  `isAnonymous` tinyint(4) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_request_waves`
--

CREATE TABLE `conversation_request_waves` (
  `id` int(10) UNSIGNED NOT NULL,
  `conversationRequestsId` int(10) UNSIGNED NOT NULL,
  `waveId` int(10) UNSIGNED NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `crisis_resources`
--

CREATE TABLE `crisis_resources` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phoneNumber` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `schoolProfileId` int(10) UNSIGNED NOT NULL,
  `serviceTypeId` varchar(255) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` int(10) NOT NULL,
  `label` varchar(250) NOT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `local_resources`
--

CREATE TABLE `local_resources` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `insuranceType` varchar(255) NOT NULL,
  `streetAddress` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zipCode` varchar(255) NOT NULL,
  `phoneNumber` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `schoolProfileId` int(10) UNSIGNED NOT NULL,
  `serviceTypeId` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `schoolProfileId` int(10) UNSIGNED NOT NULL,
  `refferedBy` int(10) UNSIGNED NOT NULL,
  `refferedTo` int(10) UNSIGNED NOT NULL,
  `studentId` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `responder_categories`
--

CREATE TABLE `responder_categories` (
  `id` int(10) NOT NULL,
  `schoolProfileId` int(10) NOT NULL,
  `levelId` int(10) NOT NULL,
  `positionName` varchar(250) NOT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `responder_profiles`
--

CREATE TABLE `responder_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `responderId` varchar(100) NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `position` varchar(50) NOT NULL,
  `schoolProfileId` int(10) UNSIGNED NOT NULL,
  `authorizationCode` varchar(50) NOT NULL,
  `isAvalable` tinyint(4) DEFAULT '0',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `responder_students`
--

CREATE TABLE `responder_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `studentProfileId` int(10) UNSIGNED NOT NULL,
  `responderProfileId` int(10) UNSIGNED NOT NULL,
  `verified` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT 'Super Admin',
  `label` varchar(100) NOT NULL DEFAULT 'super_admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `schedules_alert`
--

CREATE TABLE `schedules_alert` (
  `id` int(10) NOT NULL,
  `fromUser` int(10) NOT NULL,
  `toUser` int(10) NOT NULL,
  `message` varchar(250) DEFAULT NULL,
  `sendDate` timestamp NULL DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_sessions`
--

CREATE TABLE `schedule_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `studentProfileId` int(10) UNSIGNED NOT NULL,
  `responderProfileId` int(10) UNSIGNED NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `repeated` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 => Not Repeated , 1 => Repeated',
  `description` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `status` tinyint(4) DEFAULT '0',
  `startDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rEndDate` timestamp NULL DEFAULT NULL,
  `causeData` text NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `school_admin_profiles`
--

CREATE TABLE `school_admin_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `schoolProfileId` int(10) UNSIGNED NOT NULL,
  `firstName` varchar(200) NOT NULL,
  `lastName` varchar(200) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `school_profiles`
--

CREATE TABLE `school_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `accessCode` varchar(100) NOT NULL,
  `schoolName` varchar(255) NOT NULL,
  `schoolAddress` varchar(300) DEFAULT 'None',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `school_secondary_admin_profiles`
--

CREATE TABLE `school_secondary_admin_profiles` (
  `id` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `schoolProfileId` int(10) NOT NULL,
  `firstName` varchar(250) NOT NULL,
  `lastName` varchar(250) NOT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `schoolProfileId` int(10) UNSIGNED NOT NULL,
  `studentId` varchar(100) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `gradeLevel` varchar(50) DEFAULT NULL,
  `authorizationCode` varchar(50) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `roleId` int(10) UNSIGNED NOT NULL,
  `resetPasswordToken` varchar(255) DEFAULT NULL,
  `createdResetPToken` timestamp NULL DEFAULT NULL,
  `avatarFilePath` varchar(200) DEFAULT NULL,
  `deviceToken` varchar(200) DEFAULT NULL,
  `deviceType` tinyint(3) UNSIGNED DEFAULT '0' COMMENT '0=>Android, 1 = IOS',
  `onlineStatus` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `verified` tinyint(3) UNSIGNED NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users_chat`
--

CREATE TABLE `users_chat` (
  `id` int(10) UNSIGNED NOT NULL,
  `threadId` int(10) UNSIGNED NOT NULL,
  `fromUser` int(10) UNSIGNED NOT NULL,
  `toUser` int(10) UNSIGNED NOT NULL,
  `message` varchar(500) CHARACTER SET utf8mb4 NOT NULL,
  `read` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1 => Read, 0 => Unread',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_threads`
--

CREATE TABLE `user_threads` (
  `id` int(10) UNSIGNED NOT NULL,
  `fromUser` int(10) UNSIGNED NOT NULL,
  `toUser` int(10) UNSIGNED NOT NULL,
  `threadName` varchar(100) NOT NULL,
  `threadLabel` varchar(255) NOT NULL,
  `type` tinyint(3) UNSIGNED DEFAULT '0' COMMENT '0 = Normal, 1 = Anonimity',
  `anonimityFlag` tinyint(3) UNSIGNED DEFAULT '0' COMMENT '0 = Show, 1 = Hide',
  `causeData` text,
  `level` int(10) UNSIGNED DEFAULT '1',
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `waves`
--

CREATE TABLE `waves` (
  `id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anonimity_reports`
--
ALTER TABLE `anonimity_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schoolAdminProfileId` (`schoolProfileId`),
  ADD KEY `responderId` (`responderId`),
  ADD KEY `studentId` (`studentId`),
  ADD KEY `threadId` (`threadId`);

--
-- Indexes for table `conversation_requests`
--
ALTER TABLE `conversation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studentProfileId` (`studentProfileId`),
  ADD KEY `responderProfileId` (`responderProfileId`);

--
-- Indexes for table `conversation_request_waves`
--
ALTER TABLE `conversation_request_waves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversationRequestsId` (`conversationRequestsId`),
  ADD KEY `waveId` (`waveId`);

--
-- Indexes for table `crisis_resources`
--
ALTER TABLE `crisis_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serviceTypeId` (`serviceTypeId`),
  ADD KEY `crisis_resources_ibfk_1` (`schoolProfileId`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `local_resources`
--
ALTER TABLE `local_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schoolProfileId` (`schoolProfileId`),
  ADD KEY `serviceTypeId` (`serviceTypeId`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD KEY `schoolAdminProfileId` (`schoolProfileId`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `responder_categories`
--
ALTER TABLE `responder_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `responder_profiles`
--
ALTER TABLE `responder_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `schoolAdminProfilesId` (`schoolProfileId`);

--
-- Indexes for table `responder_students`
--
ALTER TABLE `responder_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studentProfileId` (`studentProfileId`),
  ADD KEY `responderProfileId` (`responderProfileId`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules_alert`
--
ALTER TABLE `schedules_alert`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_sessions`
--
ALTER TABLE `schedule_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `studentProfileId` (`studentProfileId`),
  ADD KEY `responderProfileId` (`responderProfileId`);

--
-- Indexes for table `school_admin_profiles`
--
ALTER TABLE `school_admin_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `schoolProfileId` (`schoolProfileId`);

--
-- Indexes for table `school_profiles`
--
ALTER TABLE `school_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_secondary_admin_profiles`
--
ALTER TABLE `school_secondary_admin_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `schoolProfileId` (`schoolProfileId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roleId` (`roleId`);

--
-- Indexes for table `users_chat`
--
ALTER TABLE `users_chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fromUser` (`fromUser`),
  ADD KEY `toUser` (`toUser`),
  ADD KEY `threadId` (`threadId`);

--
-- Indexes for table `user_threads`
--
ALTER TABLE `user_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId2` (`threadName`),
  ADD KEY `fromUser` (`fromUser`),
  ADD KEY `toUser` (`toUser`);

--
-- Indexes for table `waves`
--
ALTER TABLE `waves`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anonimity_reports`
--
ALTER TABLE `anonimity_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_requests`
--
ALTER TABLE `conversation_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_request_waves`
--
ALTER TABLE `conversation_request_waves`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crisis_resources`
--
ALTER TABLE `crisis_resources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `local_resources`
--
ALTER TABLE `local_resources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `responder_categories`
--
ALTER TABLE `responder_categories`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `responder_profiles`
--
ALTER TABLE `responder_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `responder_students`
--
ALTER TABLE `responder_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules_alert`
--
ALTER TABLE `schedules_alert`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_sessions`
--
ALTER TABLE `schedule_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_admin_profiles`
--
ALTER TABLE `school_admin_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_profiles`
--
ALTER TABLE `school_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `school_secondary_admin_profiles`
--
ALTER TABLE `school_secondary_admin_profiles`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_chat`
--
ALTER TABLE `users_chat`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_threads`
--
ALTER TABLE `user_threads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waves`
--
ALTER TABLE `waves`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anonimity_reports`
--
ALTER TABLE `anonimity_reports`
  ADD CONSTRAINT `anonimity_reports_ibfk_1` FOREIGN KEY (`schoolProfileId`) REFERENCES `school_admin_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anonimity_reports_ibfk_2` FOREIGN KEY (`responderId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anonimity_reports_ibfk_3` FOREIGN KEY (`studentId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anonimity_reports_ibfk_4` FOREIGN KEY (`threadId`) REFERENCES `user_threads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_requests`
--
ALTER TABLE `conversation_requests`
  ADD CONSTRAINT `conversation_requests_ibfk_1` FOREIGN KEY (`studentProfileId`) REFERENCES `student_profiles` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `conversation_requests_ibfk_2` FOREIGN KEY (`responderProfileId`) REFERENCES `responder_profiles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `conversation_request_waves`
--
ALTER TABLE `conversation_request_waves`
  ADD CONSTRAINT `conversation_request_waves_ibfk_1` FOREIGN KEY (`conversationRequestsId`) REFERENCES `conversation_requests` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `conversation_request_waves_ibfk_2` FOREIGN KEY (`waveId`) REFERENCES `waves` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `crisis_resources`
--
ALTER TABLE `crisis_resources`
  ADD CONSTRAINT `crisis_resources_ibfk_1` FOREIGN KEY (`schoolProfileId`) REFERENCES `school_profiles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `local_resources`
--
ALTER TABLE `local_resources`
  ADD CONSTRAINT `local_resources_ibfk_1` FOREIGN KEY (`schoolProfileId`) REFERENCES `school_profiles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`schoolProfileId`) REFERENCES `school_admin_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `responder_profiles`
--
ALTER TABLE `responder_profiles`
  ADD CONSTRAINT `responder_profiles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `responder_profiles_ibfk_2` FOREIGN KEY (`schoolProfileId`) REFERENCES `school_admin_profiles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `responder_students`
--
ALTER TABLE `responder_students`
  ADD CONSTRAINT `responder_students_ibfk_1` FOREIGN KEY (`studentProfileId`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `responder_students_ibfk_2` FOREIGN KEY (`responderProfileId`) REFERENCES `responder_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedule_sessions`
--
ALTER TABLE `schedule_sessions`
  ADD CONSTRAINT `schedule_sessions_ibfk_1` FOREIGN KEY (`studentProfileId`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `schedule_sessions_ibfk_2` FOREIGN KEY (`responderProfileId`) REFERENCES `responder_profiles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `school_admin_profiles`
--
ALTER TABLE `school_admin_profiles`
  ADD CONSTRAINT `school_admin_profiles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `school_admin_profiles_ibfk_2` FOREIGN KEY (`schoolProfileId`) REFERENCES `school_profiles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `student_profiles_ibfk_2` FOREIGN KEY (`schoolProfileId`) REFERENCES `school_profiles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `users_chat`
--
ALTER TABLE `users_chat`
  ADD CONSTRAINT `users_chat_ibfk_2` FOREIGN KEY (`fromUser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_chat_ibfk_3` FOREIGN KEY (`toUser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_chat_ibfk_4` FOREIGN KEY (`threadId`) REFERENCES `user_threads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_threads`
--
ALTER TABLE `user_threads`
  ADD CONSTRAINT `user_threads_ibfk_1` FOREIGN KEY (`fromUser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_threads_ibfk_2` FOREIGN KEY (`toUser`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
