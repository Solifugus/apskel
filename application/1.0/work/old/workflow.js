
tasks = [
    { id: 435, title: ‘first task to do’,       time_left: -5, due: ‘2011-12-14 12:30 PM’ },
    { id: 436, title: ‘second task to do’,  time_left: 15, due: ‘2011-12-14 12:30 PM’ },
    { id: 437, title: ‘third task to do’,      time_left: 25, due: ‘2011-12-14 12:30 PM’ },
    { id: 438, title: ‘fourth task to do’,    time_left: 75, due: ‘2011-12-14 12:30 PM’ }
]




task = {
    task_id: 343,
    title: ‘Do some stuff to the work objects’,
    description: ‘Long description of how to do stuff to the work objects.’,
    start_date: ‘2010-01-01’,
    due_date: ‘2011-01-01 5:00:00 PM’,
    completed_on: 2011-01-01 4:35 PM’,
    hours_estimated: 3,
    notes: ‘This is big or small bunch of notes.’,

    // These are the users to whom the task is assigned.
    assignees: [
        { user_id: 36, surname: ‘camel’, forename: 'Joe', email: ‘joe@camel.com’, hours_spent: 26.5 }
    ],

    // These are the users who's approval is required to consider this task truly completed.
    approvers: [
        { user_id: ‘tester’, surname: ‘Bishop’, forename: 'Mary', email: ‘mary@camel.com’ }
    ],

    // These are the tasks that must be completed (and approved) prior to this task becoming active.
    prerequisites: [
        { task_id: 332, title: ‘Do some stuff first’ }
        { task_id: 331, title: ‘Do some other stuff first’ }
    ],

    // These are objects to be associated with this task (files, source code, etc).
    resources: [
        { resource_id: 23, type: value1, reference: value2 }
        { resource_id: 13, type: value1, reference: value2 }
    ],

    // These are the skill types associated with this task.
    skills: [  ‘word processing’, ‘programming’ ],

    // These are system and/or user added historical events pertaining to this task.
    events: [
        { event_id: 46, when_happened: ‘2010-01-01 8:00 AM’, user_id: 43, user_surname: 'McDonald', user_forname: 'Ronald', user_email: 'ronald@mcdonalds.com',  event: ‘Task became active’ }
    ]
}


workflows = [
    { workflow_id: 1, title: ‘title of the first workflow’ }
    { workflow_id: 2, title: ‘title of the second workflow’ }
]   

workflow = {
    id: 46
    title: title of the workflow
    template: false
    activated: false
    tasks: [
        {
            id: 452,
            title: ‘title of first task in workflow’,
            hours_estimated: 3,
            hours_spent: 2.5,
            start_date: ‘2011-10-05 00:00:00 AM’,
            due_date: ‘2011-10-10 05:00:00 PM’,
            hardlinked: true
        },
        {
            id: 457,
            title: ‘title of second task in workflow’,
            hours_estimated: 1,
            hours_spent: .25,
            start_date: ‘2011-10-05 00:00:00 AM’,
            due_date: ‘2011-10-10 05:00:00 PM’,
            hardlinked: false
        },
        {
            id: 427,
            title: ‘title of third task in workflow’,
            hours_estimated: 2,
            hours_spent: .5,
            start_date: ‘2011-10-08 11:00:00 AM’,
            due_date: ‘2011-10-15 02:30:00 PM’,
            hardlinked: false
        },
]
}

