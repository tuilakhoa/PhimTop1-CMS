ALTER TABLE settings ADD COLUMN `useLogoAsFavicon` INT DEFAULT 1;
ALTER TABLE settings ADD COLUMN `faviconUrl` TEXT;
ALTER TABLE settings ADD COLUMN `appleTouchIconUrl` TEXT;
ALTER TABLE settings ADD COLUMN `ogImageUrl` TEXT;
ALTER TABLE settings ADD COLUMN `enableGoogleLogin` INT DEFAULT 0;
ALTER TABLE settings ADD COLUMN `enableMicrosoftLogin` INT DEFAULT 0;
