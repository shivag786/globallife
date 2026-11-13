-- Featured images for the three existing blog posts.
-- Images ship in storage/app/public/uploads/demo/blog/ (upload that folder to the server).
-- Only fills posts that have NO image yet, so an image an admin already uploaded is kept.
-- Safe to run more than once.
UPDATE `blog_posts` SET `featured_image` = 'uploads/demo/blog/5-daily-wellness-habits-backed-by-tradition.jpg'
 WHERE `slug` = '5-daily-wellness-habits-backed-by-tradition' AND (`featured_image` IS NULL OR `featured_image` = '');

UPDATE `blog_posts` SET `featured_image` = 'uploads/demo/blog/how-to-choose-the-right-fragrance-for-every-season.jpg'
 WHERE `slug` = 'how-to-choose-the-right-fragrance-for-every-season' AND (`featured_image` IS NULL OR `featured_image` = '');

UPDATE `blog_posts` SET `featured_image` = 'uploads/demo/blog/becoming-a-global-life-distributor-what-to-expect.jpg'
 WHERE `slug` = 'becoming-a-global-life-distributor-what-to-expect' AND (`featured_image` IS NULL OR `featured_image` = '');

-- Check: all three should show an image path.
SELECT `slug`, `featured_image` FROM `blog_posts`;
