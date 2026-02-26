<?php

namespace App\Traits;

trait AdminSeoTrait
{
    /**
     * Set SEO data for admin pages
     *
     * @param string $title
     * @param string|null $description
     * @param string|null $keywords
     * @return array
     */
    protected function setSeoData(string $title, ?string $description = null, ?string $keywords = null): array
    {
        return [
            'seo_title' => $title,
            'seo_description' => $description ?? $this->getDefaultDescription($title),
            'seo_keywords' => $keywords ?? $this->getDefaultKeywords($title),
        ];
    }

    /**
     * Get default description based on title
     *
     * @param string $title
     * @return string
     */
    private function getDefaultDescription(string $title): string
    {
        $descriptions = [
            'Dashboard' => 'Admin dashboard overview with system statistics and quick access to management tools.',
            'Users' => 'Manage user accounts, roles, permissions and user information in the admin panel.',
            'Create User' => 'Create new user accounts with role assignments and profile information.',
            'Edit User' => 'Edit user account details, roles, permissions and profile settings.',
            'User Profile' => 'View detailed user profile information, activity and account settings.',
            'Deleted Users' => 'Manage soft-deleted user accounts with restore and permanent deletion options.',
            'Roles' => 'Manage user roles and their associated permissions in the system.',
            'Create Role' => 'Create new user roles with custom permission assignments.',
            'Edit Role' => 'Edit existing role details and modify permission assignments.',
            'Role Details' => 'View role information, assigned permissions and associated users.',
            'Permissions' => 'Manage system permissions and their assignments to roles.',
            'Profile' => 'Admin profile management with personal information and account settings.',
            'Edit Profile' => 'Edit admin profile information, password and account preferences.',
            'Reports & Analytics' => 'Comprehensive reports and analytics dashboard with charts, statistics, and data insights.',
            'User Reports' => 'Detailed user analytics including growth trends, activity patterns, and role distribution.',
            'Email Reports' => 'Email analytics including delivery rates, open rates, and template performance.',
            'Login Reports' => 'Login analytics including success rates, geographic distribution, and security insights.',
            'System Reports' => 'System performance analytics including resource usage, storage, and performance metrics.',
            'Analytics Dashboard' => 'Comprehensive analytics dashboard with real-time data insights, charts, and performance metrics.',
            'User Analytics' => 'Detailed user analytics including registration trends, activity patterns, and demographic insights.',
            'Engagement Analytics' => 'User engagement analytics including session data, activity patterns, and interaction metrics.',
            'Performance Analytics' => 'System performance analytics including response times, resource usage, and optimization insights.',
        ];

        return $descriptions[$title] ?? "Admin panel for {$title} management and system administration.";
    }

    /**
     * Get default keywords based on title
     *
     * @param string $title
     * @return string
     */
    private function getDefaultKeywords(string $title): string
    {
        $baseKeywords = 'admin, dashboard, management, system, administration';
        
        $specificKeywords = [
            'Dashboard' => 'overview, statistics, analytics, monitoring',
            'Users' => 'users, accounts, profiles, user management',
            'Create User' => 'create user, new account, user registration, add user',
            'Edit User' => 'edit user, modify account, update profile, user settings',
            'User Profile' => 'user profile, account details, user information, profile view',
            'Deleted Users' => 'deleted users, soft delete, restore users, trashed accounts',
            'Roles' => 'roles, user roles, role management, access control',
            'Create Role' => 'create role, new role, role creation, add role',
            'Edit Role' => 'edit role, modify role, update role, role settings',
            'Role Details' => 'role details, role information, role permissions, role view',
            'Permissions' => 'permissions, access control, authorization, security',
            'Profile' => 'admin profile, account settings, personal information',
            'Edit Profile' => 'edit profile, update account, profile settings, account management',
            'Reports & Analytics' => 'reports, analytics, statistics, charts, dashboard, insights, data visualization',
            'User Reports' => 'user reports, user analytics, user statistics, user growth, user activity, user trends',
            'Email Reports' => 'email reports, email analytics, email statistics, delivery rates, open rates, email performance',
            'Login Reports' => 'login reports, login analytics, security reports, authentication, login trends, geographic data',
            'System Reports' => 'system reports, performance analytics, system statistics, monitoring, resource usage, storage',
            'Analytics Dashboard' => 'analytics, dashboard, insights, metrics, charts, data visualization, real-time data',
            'User Analytics' => 'user analytics, user trends, user statistics, user insights, registration trends, demographics',
            'Engagement Analytics' => 'engagement analytics, user activity, session data, interaction metrics, user engagement',
            'Performance Analytics' => 'performance analytics, system performance, response times, optimization, resource monitoring',
        ];

        $specific = $specificKeywords[$title] ?? strtolower($title);
        
        return "{$baseKeywords}, {$specific}";
    }

    /**
     * Get SEO data for view
     *
     * @param string $title
     * @param string|null $description
     * @param string|null $keywords
     * @return array
     */
    protected function getSeoDataForView(string $title, ?string $description = null, ?string $keywords = null): array
    {
        $seoData = $this->setSeoData($title, $description, $keywords);
        
        return [
            'title' => $seoData['seo_title'],
            'description' => $seoData['seo_description'],
            'keywords' => $seoData['seo_keywords'],
        ];
    }

    /**
     * Merge SEO data with view data
     *
     * @param array $viewData
     * @param string $title
     * @param string|null $description
     * @param string|null $keywords
     * @return array
     */
    protected function withSeo(array $viewData, string $title, ?string $description = null, ?string $keywords = null): array
    {
        return array_merge($viewData, $this->getSeoDataForView($title, $description, $keywords));
    }
}
