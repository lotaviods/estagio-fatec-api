## Logins Collection [/login]

### Login [POST]

+ Request (application/x-www-form-urlencoded)

- Attributes
    - `email` (required, string)
    - `password` (required, string) 
            
+ Response 200 (application/json)

        
        {
            "token": "534cee4d5def30160c4ac95663ba78b35caa69139b14bb236762ac53e2c92228",
            "expires_at": "2023-04-06T02:04:04+00:00",
        }
        
# Group Users registry

## Admin [/api/admin/register]

### Register [POST]

+ Request (application/x-www-form-urlencoded)

- Attributes
    - `invite_token` (required, string) - Invitation token recieved to use application.
    - `full_name` (required, number)
    - `email` (required, string)
    - `password` (required, string)

+ Response 200 (application/json)

    + Body

            {
                "message": "Admin created successfully",
                "token": {
                    "access_token": "48ca4a162ef37525edf9ee4f82115fabcb2603e9b010e8d3d3409bc98b4380e9",
                    "expires_at": "2023-04-06T02:31:59+00:00"
                }
            }  

## Student [/api/student/register]

### Register [POST]

+ Request (application/x-www-form-urlencoded)

- Attributes
    - `full_name` (required, number)
    - `email` (required, string)
    - `password` (required, string)
    - `ra` (required, string) - The RA (Registro do Aluno - Student Registration) is the student's identification number in the Student Registration System of the State Department of Education.

+ Response 200 (application/json)

    + Body

            {
                "message": "User created successfully",
                "token": {
                    "access_token": "ec1f2f5b2cd0d620e0cfbce20590f246ed8c8e87f7da1c98e736bf49654db964",
                    "expires_at": "2023-04-06T02:33:06+00:00"
                }
            }
            
# Group JobOffer

## Create a job offer [/api/job-offer]

### Create job offer[POST]
+ Request (application/x-www-form-urlencoded)

- Attributes
    - `company_id` (required, number) - The company id.
    - `description` (required, string) - Job offer description.
    - `target_course_id` (required, string) - The course id of job offer target.
    - `experience` (required, string) - Minimun expirence requirement.
    - `role` (required, string) - Job offer role.
    - `prom_image_url` (string) - Promotional image (not required).

+ Response 200 (application/json)

    + Body

            {
                "id": 2,
                "description": "Teste",
                "role": "Dev mobile",
                "job_experience": 1,
                "company_id": 1,
                "company_name": "Luiz Otavio da Silva",
                "company_profile_picture": null,
                "is_active": true,
                "applied_students_count": 0,
                "like_count": 0,
                "promotional_image_url": "some_url",
                "target_course": "Nome",
                "liked_by": []
            }

## Get all job offers available [/api/job-offers]

### List all [GET]

+ Response 204 (application/json)


            
+ Response 200 (application/json)

    + Body

            [
                {
                    "id": 1,
                    "description": "JobDescription",
                    "role": "Mobile Developer",
                    "job_experience": 1,
                    "company_id": 1,
                    "company_name": "Luiz Otavio da Silva",
                    "company_profile_picture": null,
                    "is_active": true,
                    "applied_students_count": 0,
                    "like_count": 0,
                    "target_course": "Nome",
                    "liked_by": []
                }
            ]
            
## Available job offers [/api/job-offers/available]
### List all available [GET]
+ Response 200 (application/json)

    + Body

            [
                {
                    "id": 1,
                    "description": "JobDescription",
                    "role": "Mobile Developer",
                    "job_experience": 1,
                    "company_id": 1,
                    "company_name": "Luiz Otavio da Silva",
                    "company_profile_picture": null,
                    "is_active": true,
                    "applied_students_count": 0,
                    "like_count": 0,
                    "target_course": "Nome",
                    "liked_by": []
                }
            ]
            
## Available job offers by course [/api/job-offers/available/course/{course_id}]

### List by course [GET]
+ Parameters
    + course_id (required, number, `1`) ... ID of the course to retrieve.
    
+ Response 200 (application/json)

    + Body

            [
                {
                    "id": 1,
                    "description": "JobDescription",
                    "role": "Mobile Developer",
                    "job_experience": 1,
                    "company_id": 1,
                    "company_name": "Luiz Otavio da Silva",
                    "company_profile_picture": null,
                    "is_active": true,
                    "applied_students_count": 0,
                    "like_count": 0,
                    "target_course": "Nome",
                    "liked_by": []
                }
            ]
            
## Likes [/api/job-offer/{id}/like]

### Like a job offer [POST]
+ Request (application/x-www-form-urlencoded)

        id=1
        student_id=12
        like=true

+ Response 200 (application/json)

# Group Courses

## Get all courses [/api/courses/detail]

### List all courses [GET]

+ Response 200 (application/json)

    + Body
    
            [
                {
                    "id": 1,
                    "name": "Nome",
                    "job_offers": 2
                }
            ]

## Create new course [/api/course]

### Register [POST]
- Requires `ROLE_ADM` role.

+ Request (application/x-www-form-urlencoded)

- Attributes
    - `name` (required, number)
    - `description` (required, string)
    
+ Response 200 (application/json)

    + Body

# Group Student

## Get student detail [/api/student/{student_id}/detail]

### Get detail [GET]
- Requires student token with same id as student_id parameter.

+ Parameters
    + student_id (required, number, `1`) ... ID of the student to retrieve.
+ Response 200 (application/json)

    + Body
    
            {
                "ra": "1190482023031",
                "email": "luiz.carvalho38aaa@fatec.sp.gov.br",
                "applied_jobs": []
            }

## Subscribe to job [/api/student/job-offer/subscribe]

### Subscribe [POST]

- Attributes
    - `job_id` (required, number)
    - `student_id` (required, number)
+ Response 200 (application/json)

    + Body
    
            {
                "ra": "1190482023031",
                "email": "luiz.carvalho38aaa@fatec.sp.gov.br",
                "applied_jobs": [
                    {
                        "id": 1,
                        "description": "JobDescription",
                        "role": "Mobile Developer",
                        "job_experience": 1,
                        "company_id": 1,
                        "company_name": "Luiz Otavio da Silva",
                        "company_profile_picture": null,
                        "is_active": true,
                        "applied_students_count": 1,
                        "like_count": 1,
                        "target_course": "Nome",
                        "liked_by": [
                            2
                        ]
                    }
                ]
            }
            
## Unsubscribe to job [/api/student/job-offer/unsubscribe]

### Unsubscribe [GET]
- Requires student token with same id as student_id parameter.

- Attributes
    - `job_id` (required, number)
    - `student_id` (required, number)
+ Response 200 (application/json)

    + Body

            