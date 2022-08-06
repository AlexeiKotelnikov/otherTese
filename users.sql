CREATE TABLE `users` (
                         `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                         `name` varchar(128) NOT NULL,
                         `second_name` varchar(128) NOT NULL,
                         `dt_birth` date NOT NULL,
                         `gender` tinyint(4) NOT NULL DEFAULT 0,
                         `city` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
