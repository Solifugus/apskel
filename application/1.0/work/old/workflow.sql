
DROP TABLE work_user_skills IF EXISTS;
CREATE TABLE work_user_skills
(
 id         INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 user_id    INTEGER,      -- refers to framework user
 skill      VARCHAR(15),  -- skill tags associated with tasks
 timeliness DOUBLE,       -- average speed of work on calendar
 efficiency DOUBLE,       -- average speed of work in hours spent
 quality    DOUBLE,       -- average non-rejection of tasks
 PRIMARY KEY(id)
);

DROP TABLE work_flows IF EXISTS;
CREATE TABLE work_flows
(
 id            INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 title         VARCHAR(200),  -- human readable title
 is_template   BOOLEAN,       -- is a template?
 is_activated  BOOLEAN,       -- is activated?
 PRIMARY KEY(id)
);

DROP TABLE work_flow_tasks IF EXISTS;
CREATE TABLE work_flow_tasks
(
 id           INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 workflow_id  INTEGER,  -- references a workflow
 task_id      INTEGER,  -- references a task
 hardlinked   BOOLEAN,  -- to be referenced (true) or copied
 PRIMARY KEY(id)
);

DROP TABLE work_tasks IF EXISTS;
CREATE TABLE work_tasks
(
 id                 INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 title              VARCHAR(30),
 description        TEXT,
 start_date         DATETIME,
 due_date           DATETIME,
 minutes_estimated  DOUBLE,
 minutes_spent      DOUBLE,
 notes              TEXT,
 PRIMARY KEY(id)
);

DROP TABLE work_task_assignees IF EXISTS;
CREATE TABLE work_task_assignees
(
 id             INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 task_id        INTEGER,  -- references the task
 user_id        INTEGER,  -- references the user
 minutes_spent  DOUBLE,   -- hours this user spent on this task
 is_approver    BOOLEAN,  -- true=approval role, false=worker role
 PRIMARY KEY(id)
);

DROP TABLE work_task_resources IF EXISTS;
CREATE TABLE work_task_resources
(
 id           INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 task_id      INTEGER,       -- references the task
 resource     CHAR(3),       -- resource type
 reference    VARCHAR(255),  -- reference to the resource
 PRIMARY KEY(id)
);

DROP TABLE work_task_prerequisites IF EXISTS;
CREATE TABLE work_task_prerequisites
(
 id           INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 task_id      INTEGER,  -- references this task
 require_id   INTEGER,  -- references a prerequisite task
 PRIMARY KEY(id)
);

DROP TABLE work_task_skills IF EXISTS;
CREATE TABLE work_task_skills
(
 id           INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 skill        VARCHAR(15),
 PRIMARY KEY(id)
);

DROP TABLE work_task_events IF EXISTS;
CREATE TABLE work_task_events
(
 id           INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 task_id      INTEGER,  -- references the task
 user_id      INTEGER,  -- who is responsible for this event
 post_date    DATETIME, -- when posted
 event        TEXT,     -- textual description of the event
 PRIMARY KEY(id)
);

DROP TABLE work_reports IF EXISTS;
CREATE TABLE work_reports
(
 id         INTEGER UNIQUE NOT NULL AUTO_INCREMENT,
 title      VARCHAR(200), -- title of the report
 activated  BOOLEAN,      -- activated or not
 query      TEXT,         -- SQL describing the report
 PRIMARY KEY(id)
);

