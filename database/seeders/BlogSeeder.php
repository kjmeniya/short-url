<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user for blog posts
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'role_id' => 1,
            ]);
        }

        // Create sample blog posts
        $blogs = [
            [
                'title' => 'Understanding Privacy Policies: A Complete Guide',
                'slug' => 'understanding-privacy-policies-complete-guide',
                'excerpt' => 'Learn everything you need to know about privacy policies, why they are important, and how to create one for your business.',
                'content' => '<h2>What is a Privacy Policy?</h2><p>A privacy policy is a legal document that explains how your business collects, uses, stores, and protects personal information from your users or customers.</p><h2>Why Do You Need One?</h2><p>Privacy policies are not just a legal requirement in many jurisdictions, but they also help build trust with your users by being transparent about your data practices.</p><h2>Key Components</h2><ul><li>Data collection practices</li><li>How data is used</li><li>Data sharing policies</li><li>User rights and choices</li><li>Security measures</li></ul><h2>Conclusion</h2><p>Having a comprehensive privacy policy is essential for any business that collects personal data. It protects both your business and your users.</p>',
                'meta_title' => 'Understanding Privacy Policies: A Complete Guide',
                'meta_description' => 'Learn everything you need to know about privacy policies, why they are important, and how to create one for your business.',
                'meta_keywords' => 'privacy policy, data protection, GDPR, legal compliance, business guide',
                'status' => 'published',
                'is_featured' => true,
                'reading_time' => 5,
                'published_at' => now(),
            ],
            [
                'title' => 'GDPR Compliance for Small Businesses',
                'slug' => 'gdpr-compliance-small-businesses',
                'excerpt' => 'A practical guide to GDPR compliance for small businesses, including key requirements and implementation steps.',
                'content' => '<h2>What is GDPR?</h2><p>The General Data Protection Regulation (GDPR) is a comprehensive data protection law that came into effect in May 2018.</p><h2>Who Does It Apply To?</h2><p>GDPR applies to any organization that processes personal data of EU residents, regardless of where the organization is located.</p><h2>Key Requirements</h2><ul><li>Lawful basis for processing</li><li>Data subject rights</li><li>Privacy by design</li><li>Data breach notifications</li><li>Data Protection Officer (DPO)</li></ul><h2>Getting Started</h2><p>Start by conducting a data audit to understand what personal data you collect and how you use it.</p>',
                'meta_title' => 'GDPR Compliance for Small Businesses - Complete Guide',
                'meta_description' => 'A practical guide to GDPR compliance for small businesses, including key requirements and implementation steps.',
                'meta_keywords' => 'GDPR, data protection, compliance, small business, EU regulation',
                'status' => 'published',
                'is_featured' => false,
                'reading_time' => 7,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Cookie Policies Explained: What You Need to Know',
                'slug' => 'cookie-policies-explained',
                'excerpt' => 'Understanding cookie policies, their importance, and how to create one that complies with current regulations.',
                'content' => '<h2>What are Cookies?</h2><p>Cookies are small text files stored on users\' devices when they visit websites. They help websites remember user preferences and track behavior.</p><h2>Types of Cookies</h2><ul><li>Essential cookies</li><li>Performance cookies</li><li>Functional cookies</li><li>Marketing cookies</li></ul><h2>Legal Requirements</h2><p>Many jurisdictions require websites to inform users about cookie usage and obtain consent for non-essential cookies.</p><h2>Best Practices</h2><p>Be transparent about your cookie usage and provide users with clear options to manage their preferences.</p>',
                'meta_title' => 'Cookie Policies Explained: What You Need to Know',
                'meta_description' => 'Understanding cookie policies, their importance, and how to create one that complies with current regulations.',
                'meta_keywords' => 'cookie policy, cookies, web tracking, GDPR, consent management',
                'status' => 'published',
                'is_featured' => false,
                'reading_time' => 4,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Terms and Conditions: Essential Elements',
                'slug' => 'terms-and-conditions-essential-elements',
                'excerpt' => 'Discover the key components that should be included in your website\'s terms and conditions to protect your business.',
                'content' => '<h2>Why Terms and Conditions Matter</h2><p>Terms and conditions establish the legal relationship between your business and your users, protecting both parties.</p><h2>Essential Elements</h2><ul><li>Acceptance of terms</li><li>User obligations</li><li>Intellectual property rights</li><li>Limitation of liability</li><li>Termination clauses</li></ul><h2>Industry-Specific Considerations</h2><p>Different industries may require specific clauses to address unique risks and regulatory requirements.</p><h2>Regular Updates</h2><p>Keep your terms and conditions current with changes in your business and applicable laws.</p>',
                'meta_title' => 'Terms and Conditions: Essential Elements for Your Business',
                'meta_description' => 'Discover the key components that should be included in your website\'s terms and conditions to protect your business.',
                'meta_keywords' => 'terms and conditions, legal protection, user agreements, business law',
                'status' => 'published',
                'is_featured' => true,
                'reading_time' => 6,
                'published_at' => now()->subDays(3),
            ],
        ];

        // Use insert for better performance during seeding
        foreach ($blogs as $blogData) {
            $existingBlog = Blog::where('slug', $blogData['slug'])->first();

            if (!$existingBlog) {
                Blog::create(array_merge($blogData, [
                    'author_id' => $user->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]));
            }
        }
    }
}
