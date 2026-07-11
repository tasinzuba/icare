-- IELTS Mock Platform Initial Database Setup
-- This file can be imported directly via phpMyAdmin

-- Test Sections
INSERT INTO `test_sections` (`id`, `name`, `slug`, `description`, `duration_minutes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Listening', 'listening', 'IELTS Listening Test', 30, 1, NOW(), NOW()),
(2, 'Reading', 'reading', 'IELTS Reading Test', 60, 1, NOW(), NOW()),
(3, 'Writing', 'writing', 'IELTS Writing Test', 60, 1, NOW(), NOW()),
(4, 'Speaking', 'speaking', 'IELTS Speaking Test', 15, 1, NOW(), NOW());

-- Subscription Plans
INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `price`, `currency`, `duration_days`, `description`, `is_active`, `is_popular`, `is_free`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Free Plan', 'free', 0, 'BDT', 0, 'Basic access to platform', 1, 0, 1, 1, NOW(), NOW()),
(2, 'Basic Plan', 'basic', 500, 'BDT', 30, 'Access to all mock tests', 1, 0, 0, 2, NOW(), NOW()),
(3, 'Premium Plan', 'premium', 1500, 'BDT', 90, 'All features with AI evaluation', 1, 1, 0, 3, NOW(), NOW());

-- Subscription Features
INSERT INTO `subscription_features` (`id`, `key`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'mock_test', 'Mock Tests', 'Access to mock tests', NOW(), NOW()),
(2, 'ai_writing_evaluation', 'AI Writing Evaluation', 'AI-powered writing evaluation', NOW(), NOW()),
(3, 'ai_speaking_evaluation', 'AI Speaking Evaluation', 'AI-powered speaking evaluation', NOW(), NOW()),
(4, 'detailed_analytics', 'Detailed Analytics', 'Advanced performance analytics', NOW(), NOW()),
(5, 'unlimited_attempts', 'Unlimited Attempts', 'Unlimited test attempts', NOW(), NOW()),
(6, 'human_evaluation_tokens', 'Human Evaluation Tokens', 'Monthly tokens for human evaluation', NOW(), NOW()),
(7, 'premium_full_tests', 'Premium Full Tests', 'Access to premium full mock tests', NOW(), NOW());

-- Plan Features Mapping
INSERT INTO `subscription_plan_features` (`subscription_plan_id`, `subscription_feature_id`, `value`, `created_at`, `updated_at`) VALUES
-- Free Plan
(1, 1, '2', NOW(), NOW()), -- 2 mock tests per month

-- Basic Plan  
(2, 1, '10', NOW(), NOW()), -- 10 mock tests per month
(2, 4, 'true', NOW(), NOW()), -- detailed analytics

-- Premium Plan
(3, 1, 'unlimited', NOW(), NOW()), -- unlimited mock tests
(3, 2, 'true', NOW(), NOW()), -- AI writing evaluation
(3, 3, 'true', NOW(), NOW()), -- AI speaking evaluation
(3, 4, 'true', NOW(), NOW()), -- detailed analytics
(3, 5, 'true', NOW(), NOW()), -- unlimited attempts
(3, 6, '5', NOW(), NOW()), -- 5 human evaluation tokens
(3, 7, 'true', NOW(), NOW()); -- premium full tests

-- Maintenance Mode
INSERT INTO `maintenance_mode` (`id`, `is_enabled`, `message`, `allowed_ips`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(1, 0, 'We are currently performing scheduled maintenance. Please check back later.', '[]', NULL, NULL, NOW(), NOW());

-- Website Settings
INSERT INTO `website_settings` (`id`, `site_name`, `site_description`, `meta_keywords`, `logo_path`, `favicon_path`, `contact_email`, `contact_phone`, `contact_address`, `social_links`, `analytics_code`, `custom_css`, `custom_js`, `created_at`, `updated_at`) VALUES
(1, 'IELTS Mock Platform', 'Practice IELTS tests online with AI evaluation', 'ielts, mock test, practice, english, exam', NULL, NULL, 'support@example.com', '+880 1234567890', 'Dhaka, Bangladesh', '{"facebook":"","twitter":"","linkedin":"","youtube":""}', NULL, NULL, NULL, NOW(), NOW());

-- Referral Settings
INSERT INTO `referral_settings` (`id`, `is_active`, `referrer_tokens`, `referrer_subscription_days`, `referee_discount_percentage`, `min_referrals_for_subscription`, `min_referrals_for_tokens`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 7, 10, 5, 3, NOW(), NOW());

-- Achievement Badges
INSERT INTO `achievement_badges` (`id`, `name`, `description`, `icon`, `type`, `requirement_type`, `requirement_value`, `reward_tokens`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'First Test', 'Complete your first mock test', 'award', 'test_completion', 'test_count', 1, 1, 1, NOW(), NOW()),
(2, 'Week Warrior', 'Complete 5 tests in a week', 'zap', 'test_completion', 'weekly_tests', 5, 2, 1, NOW(), NOW()),
(3, 'Perfect Score', 'Score 9.0 in any section', 'star', 'score_achievement', 'perfect_score', 9, 3, 1, NOW(), NOW()),
(4, 'Consistent Learner', 'Practice for 7 consecutive days', 'trending-up', 'streak', 'daily_streak', 7, 2, 1, NOW(), NOW()),
(5, 'Master of All', 'Complete all 4 sections', 'crown', 'test_completion', 'all_sections', 4, 5, 1, NOW(), NOW());

-- Token Packages
INSERT INTO `token_packages` (`id`, `name`, `tokens`, `price`, `currency`, `bonus_tokens`, `is_active`, `is_popular`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'Starter Pack', 5, 500, 'BDT', 0, 1, 0, 1, NOW(), NOW()),
(2, 'Value Pack', 10, 900, 'BDT', 2, 1, 1, 2, NOW(), NOW()),
(3, 'Premium Pack', 20, 1600, 'BDT', 5, 1, 0, 3, NOW(), NOW());

-- Note: Admin user will be created via the web installer
