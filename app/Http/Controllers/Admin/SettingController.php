<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    protected NotificationService $notificationService;
    protected FileUploadService $fileUploadService;

    public function __construct(NotificationService $notificationService, FileUploadService $fileUploadService)
    {
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settingGroups = Setting::getDynamicGroups();
        $groupedSettings = Setting::active()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return view('admin.settings.index')
            ->with('settingGroups', $settingGroups)
            ->with('groupedSettings', $groupedSettings)
            ->with('selectedGroup', null);
    }

    /**
     * Display settings for a specific group.
     */
    public function showGroup($group)
    {
        $settingGroups = Setting::getDynamicGroups();

        // Validate the group exists
        if (!array_key_exists($group, $settingGroups)) {
            abort(404, 'Settings group not found');
        }

        $groupedSettings = Setting::active()
            ->where('group', $group)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return view('admin.settings.group')
            ->with('settingGroups', $settingGroups)
            ->with('groupedSettings', $groupedSettings)
            ->with('selectedGroup', $group)
            ->with('groupName', $settingGroups[$group]['name']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $groups = Setting::getGroups();
        $types = Setting::getTypes();

        return view('admin.settings.create', compact('groups', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,boolean,select,email,url,file',
            'options' => 'nullable|array',
            'group' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_public'] = $request->has('is_public') || $request->input('is_public') === 'true';
        $data['is_active'] = $request->has('is_active') || $request->input('is_active') === 'true';

        $setting = Setting::create($data);
        Cache::forget('system_settings');

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Setting created successfully.',
                'setting' => $setting
            ]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        return response()->json([
            'id' => $setting->id,
            'name' => $setting->name,
            'key' => $setting->key,
            'type' => $setting->type,
            'group' => $setting->group,
            'description' => $setting->description,
            'value' => $setting->value,
            'options' => $setting->options,
            'sort_order' => $setting->sort_order,
            'is_public' => $setting->is_public,
            'is_active' => $setting->is_active,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        $groups = Setting::getGroups();
        $types = Setting::getTypes();

        if (request()->ajax()) {
            return view('admin.settings.partials.edit-modal', compact('setting', 'groups', 'types'))->render();
        }

        return view('admin.settings.edit', compact('setting', 'groups', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        $request->validate([
            'key' => ['required', 'string', 'max:255', Rule::unique('settings')->ignore($setting->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'nullable|string',
            'type' => 'required|in:text,textarea,number,boolean,select,email,url,file,color',
            'options' => 'nullable|array',
            'group' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_public'] = $request->has('is_public') || $request->input('is_public') === 'true';
        $data['is_active'] = $request->has('is_active') || $request->input('is_active') === 'true';

        $setting->update($data);
        Cache::forget('system_settings');

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully.'
            ]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        $setting->delete();
        Cache::forget('system_settings');

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Setting deleted successfully.'
            ]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }

    /**
     * Update multiple settings at once.
     */
    public function updateBulk(Request $request)
    {
        $settings = $request->input('settings', []);

        // Handle image uploads with cropper for all logo settings
        $logoSettings = [
            'site_favicon',
            'admin_logo_small_light',
            'admin_logo_small_dark',
            'admin_logo_light',
            'admin_logo_dark',
            'frontend_logo_small_light',
            'frontend_logo_small_dark',
            'frontend_logo_light',
            'frontend_logo_dark',
            'mobile_splash_logo_light',
            'mobile_splash_logo_dark',
            'mobile_home_logo_light',
            'mobile_home_logo_dark',
        ];

        foreach ($logoSettings as $logoKey) {
            $removeField = $logoKey . '_remove';
            $croppedField = $logoKey . '_cropped';

            // Check if image should be removed
            if ($request->input($removeField) === '1') {
                // Delete old image if exists
                $oldImage = Setting::where('key', $logoKey)->value('value');
                if ($oldImage) {
                    $this->fileUploadService->delete($oldImage);
                }

                // Set setting value to null
                Setting::where('key', $logoKey)->update(['value' => null]);
            }
            // Check if new image was uploaded
            elseif ($request->filled($croppedField)) {
                $directory = $logoKey === 'site_favicon' ? 'favicons' : 'logos';
                $imagePath = $this->fileUploadService->uploadBase64($request->input($croppedField), $directory);
                if ($imagePath) {
                    // Delete old image if exists
                    $oldImage = Setting::where('key', $logoKey)->value('value');
                    if ($oldImage) {
                        $this->fileUploadService->delete($oldImage);
                    }

                    // Update setting
                    Setting::where('key', $logoKey)->update(['value' => $imagePath]);
                }
            }
        }

        foreach ($settings as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        Cache::forget('system_settings');

        // Get group name for dynamic message
        $group = $request->input('group');
        $groups = Setting::getGroups();
        $groupName = $group && isset($groups[$group]) ? $groups[$group] : 'System Settings';

        // Send notification to super admins for settings updates
        $currentUser = Auth::user();
        $settingsCount = count($settings);
        $this->notificationService->sendToSuperAdmins(
            'settings_updated',
            "{$groupName} Updated",
            "{$groupName} have been updated by {$currentUser->name}. {$settingsCount} settings were modified.",
            [
                'settings_count' => $settingsCount,
                'updated_by' => $currentUser->name,
                'settings_keys' => array_keys($settings),
                'url' => $group ? route('admin.settings.group', $group) : route('admin.settings.index')
            ]
        );

        $successMessage = "{$groupName} updated successfully.";

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage
            ]);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', $successMessage);
    }

    /**
     * Verify super admin password for settings changes
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();

        // Only super admin can confirm settings changes
        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only super admin can modify settings.'
            ], 403);
        }

        // Verify password
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => true,
                'message' => 'Password verified successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Incorrect password.'
        ], 401);
    }

    /**
     * Logout user from other devices.
     */
    public function logoutOtherDevices(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $request->password,
        ])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['password' => 'The provided password is incorrect.']
                ], 422);
            }

            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        // Use Laravel's built-in method to logout other devices
        Auth::logoutOtherDevices($request->password);

        // Also use our custom method for additional cleanup
        $currentSessionId = session()->getId();
        $deletedSessions = Auth::user()->logoutOtherDevices($currentSessionId);

        $message = "You have been logged out from {$deletedSessions} other device(s).";

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Logout all users (Super Admin only).
     */
    public function logoutAllUsers(Request $request)
    {
        // Check if user is super admin (role_id = 1)
        if (Auth::user()->role_id !== 1) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify the admin's password
        if (!Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $request->password,
        ])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['password' => 'The provided password is incorrect.']
                ], 422);
            }

            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        $totalLoggedOut = 0;

        // Get all active sessions count before deletion
        $sessionTable = config('session.table', 'sessions');

        if (config('session.driver') === 'database') {
            $totalLoggedOut = DB::table($sessionTable)->count();
            DB::table($sessionTable)->truncate();
        }

        // Clear all user remember tokens
        User::query()->update(['remember_token' => null]);

        $message = "All users have been logged out successfully. Total sessions cleared: {$totalLoggedOut}";

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Test SMTP connection.
     */
    public function testSmtp()
    {
        try {
            $configService = app(\App\Services\ConfigurationService::class);
            $result = $configService->testSmtpConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to default values.
     */
    public function resetToDefaults(Request $request)
    {
        try {
            $settingsService = app(\App\Services\SettingsService::class);

            $group = $request->input('group');
            $keys = $request->input('keys');

            $resetCount = $settingsService->resetToDefaults($keys, $group);

            $message = $group
                ? "Reset {$resetCount} settings in '{$group}' group to default values."
                : ($keys
                    ? "Reset " . count($keys) . " selected settings to default values."
                    : "Reset {$resetCount} settings to default values.");

            return response()->json([
                'success' => true,
                'message' => $message,
                'reset_count' => $resetCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download database backup with password confirmation
     */
    public function downloadDatabase(Request $request)
    {
        try {
            // Validate password
            $request->validate([
                'password' => 'required|string',
                'format' => 'required|in:sql,csv'
            ]);

            $user = Auth::user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password. Please try again.'
                ], 422);
            }

            // Check if user has permission to download database
            if (!$user->hasPermission('admin.settings.download-database')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to download database.'
                ], 403);
            }

            $format = $request->format;
            $timestamp = now()->format('Y_m_d_H_i_s');

            if ($format === 'sql') {
                return $this->downloadDatabaseSql($timestamp);
            } else {
                return $this->downloadDatabaseCsv($timestamp);
            }
        } catch (\Exception $e) {
            Log::error('Database download failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to download database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download database as SQL dump
     */
    private function downloadDatabaseSql($timestamp)
    {
        $databaseName = config('database.connections.mysql.database');
        $filename = "database_backup_{$timestamp}.sql";

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $databaseName;

        $sql = "-- Database Backup\n";
        $sql .= "-- Generated on: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$databaseName}\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            $sql .= "-- Table structure for `{$tableName}`\n";
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                $sql .= "-- Data for table `{$tableName}`\n";
                $sql .= "INSERT INTO `{$tableName}` VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowData = [];
                    foreach ((array)$row as $value) {
                        if (is_null($value)) {
                            $rowData[] = 'NULL';
                        } else {
                            $rowData[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(',', $rowData) . ')';
                }

                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($sql)
        ]);
    }

    /**
     * Download database as CSV files in ZIP
     */
    private function downloadDatabaseCsv($timestamp)
    {
        $databaseName = config('database.connections.mysql.database');
        $filename = "database_backup_{$timestamp}.zip";
        $tempPath = storage_path('app/temp');

        // Create temp directory if it doesn't exist
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $zipPath = $tempPath . '/' . $filename;
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Cannot create ZIP file');
        }

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $databaseName;

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            // Get table data
            $rows = DB::table($tableName)->get();

            if ($rows->count() > 0) {
                $csvContent = '';

                // Get column names
                $columns = Schema::getColumnListing($tableName);
                $csvContent .= implode(',', $columns) . "\n";

                // Add data rows
                foreach ($rows as $row) {
                    $rowData = [];
                    foreach ($columns as $column) {
                        $value = $row->$column ?? '';
                        // Escape CSV values
                        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
                            $value = '"' . str_replace('"', '""', $value) . '"';
                        }
                        $rowData[] = $value;
                    }
                    $csvContent .= implode(',', $rowData) . "\n";
                }

                $zip->addFromString($tableName . '.csv', $csvContent);
            }
        }

        $zip->close();

        // Return the ZIP file
        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Send test email to verify email configuration
     */
    public function sendTestEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'subject' => 'nullable|string|max:255',
                'message' => 'nullable|string|max:1000'
            ]);

            $toEmail = $request->email;
            $subject = $request->subject ?: 'Test Email';
            $message = $request->message ?: 'This is a test email to verify your email configuration is working correctly.';

            // Send test email using EmailService with template
            $emailService = app(\App\Services\EmailService::class);
            $success = $emailService->sendTemplateEmail('test-email', $toEmail, [
                'subject' => $subject,
                'message' => $message,
                'app_name' => site_name(),
                'app_url' => app_url(),
            ]);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "Test email sent successfully to {$toEmail}"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to send test email to {$toEmail}"
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }
}
