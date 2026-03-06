CREATE DATABASE circulo_crecimiento;
USE circulo_crecimiento;

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `content_text` text NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activities` (`id`, `module_id`, `content_text`, `position`) VALUES
(1, 1, 'Si hoy perdieras la memoria y solo pudieras ver tus acciones de la última semana, ¿qué tipo de persona concluirías que eres?', 1),
(2, 1, '¿Qué beneficio "secreto" o comodidad obtienes al mantenerte en tu estado actual?', 2),
(3, 1, '¿En qué áreas de tu vida estás aceptando un estándar "mínimo aceptable" en lugar de uno de excelencia?', 3),
(4, 1, 'Si tuvieras que apostar todo tu dinero a que lograrás tu meta basándote solo en tus acciones de ayer, ¿lo harías? ¿Por qué?', 4),
(5, 1, '¿Qué parte de tu identidad actual ya no encaja con la vida que quieres construir?', 5);


CREATE TABLE `individual_assignments` (
  `id` int(11) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `modules_challenges` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('modulo','reto_semanal','reto_mensual','reto_individual') NOT NULL,
  `numeric_order` decimal(5,2) NOT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unlock_keyword` varchar(50) DEFAULT NULL,
  `module_group` varchar(100) DEFAULT 'General',
  `duration_days` int(11) DEFAULT 7,
  `audio_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `modules_challenges` (`id`, `title`, `description`, `type`, `numeric_order`, `is_premium`, `active`, `created_at`, `unlock_keyword`, `module_group`, `duration_days`, `audio_url`) VALUES
(1, 'Arquitectura de Identidad', 'Exploración profunda de los pilares que sostienen tu autopercepción y hábitos actuales.', 'modulo', 1.00, 0, 1, '2026-03-05 18:57:10', 'IDENTIDAD', 'General', 7, NULL);


CREATE TABLE `subscription_tiers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `subscription_tiers` (`id`, `name`, `created_at`) VALUES
(1, 'Free', '2026-03-05 17:28:53'),
(2, 'Pro', '2026-03-05 17:28:53'),
(3, 'Ultra', '2026-03-05 17:28:53');


CREATE TABLE `users` (
  `id` binary(16) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `tier_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `users` (`id`, `email`, `password_hash`, `tier_id`, `status`, `created_at`) VALUES
(0x81a0096f6c4c4e31a856acc8af974563, 'samuelnahum.o@gmail.com', '$2b$10$o0IC9YMb3PVYyyEmWpUu0uBNvLXdcobPLiFBGU2jr.4O5CDrKxRku', 1, 'active', '2026-03-05 17:28:59');


CREATE TABLE `user_activity_responses` (
  `id` bigint(20) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `response_content` text DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 1,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `user_activity_responses` (`id`, `user_id`, `activity_id`, `response_content`, `is_completed`, `answered_at`) VALUES
(1, 0x81a0096f6c4c4e31a856acc8af974563, 1, 'Mi respuesta aquí sobre la arquitectura de identidad...', 1, '2026-03-05 19:40:16');


CREATE TABLE `user_module_progress` (
  `id` bigint(20) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `module_id` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_completed` tinyint(1) DEFAULT 1,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `user_profiles` (
  `user_id` binary(16) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `marital_status` enum('soltero','casado','divorciado','viudo','otro') DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `user_profiles` (`user_id`, `first_name`, `last_name`, `phone`, `country`, `city`, `marital_status`, `age`, `updated_at`) VALUES
(0x81a0096f6c4c4e31a856acc8af974563, 'Nahum', 'Gonza', NULL, NULL, NULL, NULL, NULL, '2026-03-05 17:28:59');


ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_module` (`module_id`);


ALTER TABLE `individual_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ind_user` (`user_id`),
  ADD KEY `fk_ind_challenge` (`challenge_id`);


ALTER TABLE `modules_challenges`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `subscription_tiers`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_tier` (`tier_id`);


ALTER TABLE `user_activity_responses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_activity` (`user_id`,`activity_id`),
  ADD KEY `fk_res_activity` (`activity_id`);


ALTER TABLE `user_module_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_module` (`user_id`,`module_id`),
  ADD KEY `fk_prog_module` (`module_id`);


ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);


ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `individual_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `modules_challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `subscription_tiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `user_activity_responses`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `user_module_progress`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;


ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activity_module` FOREIGN KEY (`module_id`) REFERENCES `modules_challenges` (`id`) ON DELETE CASCADE;


ALTER TABLE `individual_assignments`
  ADD CONSTRAINT `fk_ind_challenge` FOREIGN KEY (`challenge_id`) REFERENCES `modules_challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ind_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_tier` FOREIGN KEY (`tier_id`) REFERENCES `subscription_tiers` (`id`);


ALTER TABLE `user_activity_responses`
  ADD CONSTRAINT `fk_res_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_res_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


ALTER TABLE `user_module_progress`
  ADD CONSTRAINT `fk_prog_module` FOREIGN KEY (`module_id`) REFERENCES `modules_challenges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prog_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Create an admin user for testing panel
INSERT INTO `users` (`id`, `email`, `password_hash`, `tier_id`, `status`, `created_at`) VALUES
(0x11111111111111111111111111111111, 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'active', '2026-03-05 17:28:59');
INSERT INTO `user_profiles` (`user_id`, `first_name`, `last_name`, `updated_at`) VALUES
(0x11111111111111111111111111111111, 'Admin', 'Admin', '2026-03-05 17:28:59');
