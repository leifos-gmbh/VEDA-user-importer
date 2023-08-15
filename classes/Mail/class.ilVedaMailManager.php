<?php

class ilVedaMailManager
{
    protected ilMailMimeSenderFactory $sender_factory;
    protected ilVedaMailSegmentRepository $mail_segment_repo;
    protected ilLogger $veda_logger;
    protected ilVedaConnectorSettings $veda_settings;

    public function __construct() {
        global $DIC;
        $this->sender_factory = $DIC["mail.mime.sender.factory"];
        $this->veda_logger = $DIC->logger()->vedaimp();
        $this->mail_segment_repo = (new ilVedaRepositoryFactory())->getMailRepository();
        $this->veda_settings = new ilVedaConnectorSettings();
    }

    public function sendStatus(): void {
        $this->veda_logger->debug('Sending Mail');

        $mail_segments = $this->mail_segment_repo->lookupMailSegments();
        $mail_segments_errors = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::ERROR);
        $body = '';
        $subject = 'ilVedaConnectorPlugin Status';

        if(count($mail_segments_errors) === 0) {
            $body .= 'Gesamtstatus erfolgreich!' . "\n\n";
        }
        if(count($mail_segments_errors) > 0) {
            $body .= 'Während der Aktualisierung sind Fehler aufgetreten:' . "\n\n";
            foreach ($mail_segments_errors as $error_segment) {
                $body .= $error_segment->getMessage() . "\n";
            }
            $body .= "\n\n";
        }

        $segments_user_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::USER_UPDATED);
        $body .= 'Anzahl aktualisierter, oder generierter Nutzerkonten:' . count($segments_user_updated) . "\n";

        $segments_courses_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::COURSE_UPDATED);
        $body .= 'Anzahl aktualisierter, oder generierter Kurse:' . count($segments_courses_updated) . "\n";

        $segments_mmbrshp_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED);
        $body .= 'Anzahl aktualisierter, oder generierter Midgliedschaften:' . count($segments_mmbrshp_updated) . "\n";

        $body .= 'Diese Email wurde automatisch generiert.';

        $mmail = new ilMimeMail();
        $mmail->From($this->sender_factory->system());
        $mmail->Subject($subject, true);
        $mmail->To($this->veda_settings->getMailTargets());
        $mmail->Body($body);

        $this->veda_logger->debug("\n" . $body);

        $this->dumpMail($mmail);

        $mmail->Send();

        $this->clearMailData();
    }

    public function sendSIFACourseCompleted() {
        $this->veda_logger->debug('Sending Mail');

        $mail_segments = $this->mail_segment_repo->lookupMailSegments();
        $mail_segments_errors = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::ERROR);
        $body = '';
        $subject = 'ilVedaConnectorPlugin Status SIFA Kurs importiert';

        if(count($mail_segments_errors) === 0) {
            $body .= 'SIFA Kurs erfolgreich importiert!' . "\n\n";
        }
        if(count($mail_segments_errors) > 0) {
            $body .= 'Während der Aktualisierung sind Fehler aufgetreten:' . "\n\n";
            foreach ($mail_segments_errors as $error_segment) {
                $body .= $error_segment->getMessage() . "\n";
            }
            $body .= "\n\n";
        }

        $segments_user_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::USER_UPDATED);
        $body .= 'Anzahl aktualisierter, oder generierter Nutzerkonten:' . count($segments_user_updated) . "\n";

        $segments_courses_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::COURSE_UPDATED);
        $body .= 'Anzahl aktualisierter, oder generierter Kurse:' . count($segments_courses_updated) . "\n";

        $segments_mmbrshp_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED);
        $body .= 'Anzahl aktualisierter, oder generierter Midgliedschaften:' . count($segments_mmbrshp_updated) . "\n";

        $body .= "\n";
        $body .= 'Diese Email wurde automatisch generiert.';

        $mmail = new ilMimeMail();
        $mmail->From($this->sender_factory->system());
        $mmail->Subject($subject, true);
        $mmail->To($this->veda_settings->getMailTargets());
        $mmail->Body($body);

        $this->veda_logger->debug("\n" . $body);

        $this->dumpMail($mmail);

        $mmail->Send();

        $this->clearMailData();
    }

    public function clearMailData(): void
    {
        $this->mail_segment_repo->deleteAllMailSegments();
    }

    protected function dumpMail(
        ilMimeMail $mail
    ): void {
        $this->veda_logger->debug('From:' . $mail->getFrom()->getFromAddress());
        $this->veda_logger->debug('Subject:' . $mail->getSubject());
        $this->veda_logger->debug('To: ' . implode(',', $mail->getTo()));
        $this->veda_logger->debug('Body:' . $mail->getFinalBody());
    }
}