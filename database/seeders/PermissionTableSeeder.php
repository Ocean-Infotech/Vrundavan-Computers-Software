<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'global' => [
                'no-permission',
                'panel-user-managment',
                'master-module',
            ],
            'users' => [
                'users-list',
                'users-create',
                'users-edit',
                'users-delete',
            ],
            'roles' => [
                'roles-list',
                'roles-create',
                'roles-edit',
                'roles-delete',
            ],
            'permissions' => [
                'permissions-list',
                'permissions-create',
                'permissions-edit',
                'permissions-delete',
            ],
            'country' => [
                'country-list',
                'country-create',
                'country-edit',
                'country-delete',
            ],
            'state' => [
                'state-list',
                'state-create',
                'state-edit',
                'state-delete',
            ],
            'city' => [
                'city-list',
                'city-create',
                'city-edit',
                'city-delete',
            ],
            'Pincode' => [
                'pincode-list',
                'pincode-create',
                'pincode-edit',
                'pincode-delete',
            ],
            'Admission' => [
                'admission-list',
                'admission-create',
                'admission-edit',
                'admission-delete',
            ],
            'Attedance' => [
                'attedance-list',
                'attedance-create',
                'attedance-edit',
                'attedance-delete',
            ],
            'Fees-collection' => [
                'fees-collection-list',
                'fees-collection-create',
                'fees-collection-edit',
                'fees-collection-delete',
            ],
            'Issue-certificate' => [
                'issue-certificate-bonafide-list',
                'issue-certificate-tc-list',
                'issue-certificate-english-medium-list',
            ],
            'Faculty-complaint-report' => [
                'faculty-complaint-report-list',
                'faculty-complaint-report-create',
                'faculty-complaint-report-edit',
                'faculty-complaint-report-delete',
            ],
            'Supplier Contact' => [
                'supplier-list',
                'supplier-create',
                'supplier-edit',
                'supplier-delete',
            ],
            'Other-list' => [
                'other-list-list',
            ],
            'Other-list-selection' => [
                'other-list-selection-list',
            ],
            'test' => [
                'test-list',
                'test-create',
                'test-edit',
                'test-delete',
            ],
            'test_report' => [
                'test_report-list',
                'test_report-create',
                'test_report-edit',
                'test_report-delete',
            ],
            'course' => [
                'course-list',
                'course-create',
                'course-edit',
                'course-delete',
            ],
            'batch' => [
                'batch-list',
                'batch-create',
                'batch-edit',
                'batch-delete',
            ],
            'class' => [
                'class-list',
                'class-create',
                'class-edit',
                'class-delete',
            ],
            'shift' => [
                'shift-list',
                'shift-create',
                'shift-edit',
                'shift-delete',
            ],
            'syllabus' => [
                'syllabus-list',
                'syllabus-create',
                'syllabus-edit',
                'syllabus-delete',
            ],
            'subject' => [
                'subject-list',
                'subject-create',
                'subject-edit',
                'subject-delete',
            ],
            'system-user' => [
                'system-user-list',
                'system-user-create',
                'system-user-edit',
                'system-user-delete',
            ],
            'Application' => [
                'about-us',
            ],
            'Setting' => [
                'generate-api-documents',
            ],
            'assessment' => [
                'assessment-list',
                'assessment-create',
                'assessment-edit',
                'assessment-delete',
            ],
            'assignment' => [
                'assignment-list',
                'assignment-create',
                'assignment-edit',
                'assignment-delete',
            ],
            'task' => [
                'task-list',
                'task-create',
                'task-edit',
                'task-delete',
            ],
            'student_report' => [
                'student_report-list',
            ],
            'event' => [
                'event-list',
                'event-create',
                'event-edit',
                'event-delete',
            ],
            'holiday' => [
                'holiday-list',
                'holiday-create',
                'holiday-edit',
                'holiday-delete',
            ],
            'achievement' => [
                'achievement-list',
                'achievement-create',
                'achievement-edit',
                'achievement-delete',
            ],
            'result' => [
                'result-list',
                'result-create',
                'result-edit',
                'result-delete',
                'result-report',
            ],
            'faculty-attendance' => [
                'faculty-attendance-list',
            ],
            'marksheet-issue' => [
                'marksheet-issue-list',
                'marksheet-issue-create',
                'marksheet-issue-edit',
                'marksheet-issue-delete',
            ]
        ];

        // Permission::truncate();

        foreach ($permissions as $groupKey => $permissionGroup) {
            foreach ($permissionGroup as $key => $permission) {
                // dd($groupKey, $permissionGroup, $key, $permission);
                Permission::firstOrCreate(['name' => $permission], ['group' => $groupKey]);
            }
        }
    }
}
