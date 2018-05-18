CREATE TABLE `shortlink` (
  `id` int(11) NOT NULL,
  `randstr` varchar(16) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `shortlink`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `randstr` (`randstr`);

ALTER TABLE `shortlink`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
