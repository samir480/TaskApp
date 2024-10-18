<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('unauthenticated user cannot create task', function () {
    Storage::fake('public');
    // Task creation data
    $data = [
        'subject' => 'test subject',
        'description' => 'test description',
        'start_date' => '18-10-2024',
        'due_date' => '22-10-2024',
        'status' => 'new',
        'priority' => 'high',
        'notes' => [
            [
                'subject' => 'Note 1',
                'note' => 'First note content',
                'attachments' => [UploadedFile::fake()->create('attachment1.pdf', 100)]
            ],
            [
                'subject' => 'Note 2',
                'note' => 'Second note content',
                'attachments' => [UploadedFile::fake()->create('attachment2.jpg', 200)]
            ]
        ]
    ];

    // Call API endpoint to create a task without authentication
    $response = $this->postJson('/api/tasks/create', $data);

    // Assert that the user is not authenticated
    $response->assertStatus(401);  // Unauthorized
});


test('validation', function () {


    $user = User::factory()->create();
    $this->actingAs($user, 'api');


    $testCases = [
        [
            'data' => [
                'subject' => '',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => 'high',
            ],
            'expectedError' => ['subject' => ['The subject field is required.']],
            'description' => 'subject is required'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => '',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => 'high',
            ],
            'expectedError' => ['description' => ['The description field is required.']],
            'description' => 'The description is required.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => 'high',
            ],
            'expectedError' => ['start_date' => ['The start date field is required.']],
            'description' => 'The start date is required.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '',
                'status' => 'new',
                'priority' => 'high',
            ],
            'expectedError' => ['due_date' => ['The due date field is required.']],
            'description' => 'The due date is required.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => '',
                'priority' => 'high',
            ],
            'expectedError' => ['status' => ['The status field is required.']],
            'description' => 'The status is required.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'abc',
                'priority' => 'high',
            ],
            'expectedError' => ['status' => ['The selected status is invalid.']],
            'description' => 'The selected status is invalid.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => '',
            ],
            'expectedError' => ['priority' => ['The priority field is required.']],
            'description' => 'The priority field is required.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => 'abc',
            ],
            'expectedError' => ['priority' => ['The selected priority is invalid.']],
            'description' => 'The selected priority is invalid.'
        ],
        // with note
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => 'high',
                'notes' => [
                    [
                        'note' => 'First note content',
                        'attachments' => [UploadedFile::fake()->create('attachment1.pdf', 100)]
                    ]
                ]
            ],
            'expectedError' => ['notes.0.subject' => ['The notes.0.subject field is required.']],
            'description' => 'The notes.0.subject field is required.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => 'high',
                'notes' => [
                    [
                        'subject' => 'Note 1',
                        'attachments' => [UploadedFile::fake()->create('attachment1.pdf', 100)]
                    ]
                ]
            ],
            'expectedError' => ['notes.0.note' => ['The notes.0.note field is required.']],
            'description' => 'The notes.0.note field is required.'
        ],
        [
            'data' => [
                'subject' => 'test subject',
                'description' => 'test description',
                'start_date' => '18-10-2024',
                'due_date' => '22-10-2024',
                'status' => 'new',
                'priority' => 'high',
                'notes' => [
                    [
                        'subject' => 'Note 1',
                        'note' => 'First note content'
                    ]
                ]
            ],
            'expectedError' => ['notes.0.attachments' => ['The notes.0.attachments field is required.']],
            'description' => 'The notes.0.attachments field is required.'
        ]
    ];

    foreach ($testCases as $testCase) {
        // Call the API 
        $response = $this->postJson('/api/tasks/create', $testCase['data']);
        Log::info(json_encode($response));
        // Assert
        $response->assertStatus(422)
            ->assertJson([
                'errors' => $testCase['expectedError']
            ], $testCase['description']); // description 
    }
});

test('user can create a task with notes and attachments', function () {
    // Fake file storage
    Storage::fake('public');

    // Create a user and authenticate
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    // Data for task and notes
    $data = [
        'subject' => 'test subject',
        'description' => 'test description',
        'start_date' => '18-10-2024',
        'due_date' => '22-10-2024',
        'status' => 'new',
        'priority' => 'high',
        'notes' => [
            [
                'subject' => 'Note 1',
                'note' => 'First note content',
                'attachments' => [UploadedFile::fake()->create('attachment1.pdf', 100)]
            ],
            [
                'subject' => 'Note 2',
                'note' => 'Second note content',
                'attachments' => [UploadedFile::fake()->create('attachment2.jpg', 200)]
            ]
        ]
    ];

    // Post the data to the task creation endpoint
    $response = $this->postJson('/api/tasks/create', $data);

    // Assert the response and database state
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Task created successfully with associated notes and attachments.',

        ]);

    $this->assertDatabaseHas('tasks', ['subject' => 'test subject']);
    $this->assertDatabaseHas('notes', ['subject' => 'Note 1']);
    $this->assertDatabaseHas('notes', ['subject' => 'Note 2']);

    // Assert that files are stored
    Storage::disk('public')->assertExists('attachments/' . $data['notes'][0]['attachments'][0]->hashName());
    Storage::disk('public')->assertExists('attachments/' . $data['notes'][1]['attachments'][0]->hashName());
});

test('user can view a task', function () {
    // Create and authenticate a user
    $user = User::factory()->create();
    $this->actingAs($user, 'api');

    // Create a task associated with the user
    $data = [
        'subject' => 'test subject',
        'description' => 'test description',
        'start_date' => '18-10-2024',
        'due_date' => '22-10-2024',
        'status' => 'new',
        'priority' => 'high',
        'notes' => [
            [
                'subject' => 'Note 1',
                'note' => 'First note content',
                'attachments' => [UploadedFile::fake()->create('attachment1.pdf', 100)]
            ],
            [
                'subject' => 'Note 2',
                'note' => 'Second note content',
                'attachments' => [UploadedFile::fake()->create('attachment2.jpg', 200)]
            ]
        ]
    ];

    // Call the API to view the task
    $this->postJson('/api/tasks/create', $data);

    //filter
    $response = $this->getJson('/api/tasks');


    // Assert success
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'tasks' => [
                '*' => [
                    'id',
                    'subject',
                    'description',
                    'due_date',
                    'status',
                    'priority',
                    'created_at',
                    'updated_at',
                    'notes_count',
                    'notes' => [
                        '*' => [
                            'id',
                            'task_id',
                            'subject',
                            'note',
                            'attachments',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                ],
            ],
        ]);
});
test('unauthenticated user cannot view task', function () {
    $response = $this->getJson('/api/tasks');
    $response->assertStatus(401);  // Unauthorized
});
