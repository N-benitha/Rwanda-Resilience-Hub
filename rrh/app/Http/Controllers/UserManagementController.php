<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $userType = $request->get('user_type');
        $status = $request->get('status');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($userType) {
            $query->where('user_type', $userType);
        }

        if ($status === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($status === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        $users = $query->latest()->paginate(20);
        $statistics = $this->getUserStatistics();

        return view('dashboard.user-management', compact('users', 'statistics', 'search', 'userType', 'status'));
    }

    public function show(User $user)
    {
        $user->load(['reports' => function ($query) {
            $query->latest()->take(10);
        }]);

        $userActivity = $this->getUserActivity($user);

        return view('users.show', compact('user', 'userActivity'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:admin,user,analyst',
            'email_verified' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'email_verified_at' => $request->email_verified ? now() : null,
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'user_type' => 'required|in:admin,user,analyst',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting the last admin
        if ($user->user_type === 'admin' && User::where('user_type', 'admin')->count() <= 1) {
            return redirect()->back()
                ->with('error', 'Cannot delete the last admin user.');
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
            $message = 'User account deactivated.';
        } else {
            $user->update(['email_verified_at' => now()]);
            $message = 'User account activated.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,change_type',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'new_user_type' => 'required_if:action,change_type|in:admin,user,analyst',
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;

        // Prevent actions on current user
        if (in_array(auth()->id(), $userIds)) {
            return redirect()->back()
                ->with('error', 'You cannot perform bulk actions on your own account.');
        }

        $users = User::whereIn('id', $userIds);

        switch ($action) {
            case 'activate':
                $users->update(['email_verified_at' => now()]);
                $message = 'Selected users have been activated.';
                break;

            case 'deactivate':
                $users->update(['email_verified_at' => null]);
                $message = 'Selected users have been deactivated.';
                break;

            case 'delete':
                // Check if trying to delete all admins
                $adminCount = User::where('user_type', 'admin')->count();
                $selectedAdmins = $users->where('user_type', 'admin')->count();
                
                if ($selectedAdmins >= $adminCount) {
                    return redirect()->back()
                        ->with('error', 'Cannot delete all admin users.');
                }

                $users->delete();
                $message = 'Selected users have been deleted.';
                break;

            case 'change_type':
                $users->update(['user_type' => $request->new_user_type]);
                $message = "Selected users' type changed to {$request->new_user_type}.";
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $users = User::select('id', 'name', 'email', 'user_type', 'email_verified_at', 'created_at')
            ->get();

        $filename = 'users_export_' . now()->format('Y_m_d_H_i_s');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function () use ($users) {
                $file = fopen('php://output', 'w');
                
                // Add CSV headers
                fputcsv($file, ['ID', 'Name', 'Email', 'User Type', 'Email Verified', 'Created At']);
                
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->user_type,
                        $user->email_verified_at ? 'Yes' : 'No',
                        $user->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // JSON export
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}.json\"",
        ];

        return response()->json($users->toArray(), 200, $headers);
    }

    public function activityLog(User $user)
    {
        $activities = $this->getUserActivity($user, 50);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'activities' => $activities,
        ]);
    }

    protected function getUserStatistics()
    {
        return [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'by_type' => User::groupBy('user_type')->selectRaw('user_type, count(*) as count')->pluck('count', 'user_type'),
            'new_users_this_month' => User::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'active_users_today' => User::whereNotNull('email_verified_at')
                ->where('updated_at', '>=', Carbon::today())
                ->count(),
        ];
    }

    protected function getUserActivity(User $user, $limit = 10)
    {
        // In a real application, you'd have an activity log table
        // For now, we'll simulate activity based on related models
        $activities = collect();

        // Recent reports
        $reports = $user->reports()->latest()->take($limit)->get();
        foreach ($reports as $report) {
            $activities->push([
                'type' => 'report_created',
                'description' => "Created report: {$report->title}",
                'timestamp' => $report->created_at,
                'details' => [
                    'report_id' => $report->id,
                    'report_type' => $report->report_type,
                ],
            ]);
        }

        // Account updates (simplified)
        $activities->push([
            'type' => 'profile_updated',
            'description' => 'Profile information updated',
            'timestamp' => $user->updated_at,
            'details' => null,
        ]);

        $activities->push([
            'type' => 'account_created',
            'description' => 'Account created',
            'timestamp' => $user->created_at,
            'details' => null,
        ]);

        return $activities->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->map(function ($activity) {
                return [
                    'type' => $activity['type'],
                    'description' => $activity['description'],
                    'timestamp' => $activity['timestamp']->toISOString(),
                    'time_ago' => $activity['timestamp']->diffForHumans(),
                    'details' => $activity['details'],
                ];
            });
    }
}