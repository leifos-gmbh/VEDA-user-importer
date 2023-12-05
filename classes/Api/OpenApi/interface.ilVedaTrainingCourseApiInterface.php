<?php

use OpenAPI\Client\Model\Ausbildungsgang;

interface ilVedaTrainingCourseApiInterface
{
    public function getCourse(string $training_course_id) : ?Ausbildungsgang;
}
