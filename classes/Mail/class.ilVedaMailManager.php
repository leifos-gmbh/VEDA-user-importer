<?php

class ilVedaMailManager
{
    protected ilMailMimeSenderFactory $sender_factory;
    protected ilVedaMailSegmentRepository $mail_segment_repo;
    protected ilLogger $veda_logger;
    protected ilVedaConnectorSettings $veda_settings;

    public function __construct()
    {
        global $DIC;
        $this->sender_factory = $DIC["mail.mime.sender.factory"];
        $this->veda_logger = $DIC->logger()->vedaimp();
        $this->mail_segment_repo = (new ilVedaRepositoryFactory())->getMailRepository();
        $this->veda_settings = new ilVedaConnectorSettings();
    }

    public function sendStatus() : void
    {
        $this->veda_logger->debug('Sending Status Mail');
        $mail_segments = $this->mail_segment_repo->lookupMailSegments();
        $mail_segments_errors = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::ERROR);
        $body = '';
        $subject = '';
        if (count($mail_segments_errors) === 0) {
            $this->veda_logger->debug('Status Mail NOT send, no errors to report');
            return;
        }
        if (count($mail_segments_errors) > 0) {
            $body .= 'Während der Aktualisierung sind Fehler aufgetreten:' . "\n\n";
            $body = $this->addSegmentMessagesToBody($body, $mail_segments_errors);
            $body .= "\n";
            $subject = 'FEHLER ilVedaConnectorPlugin import';
        }
        $body = $this->addImportInfoToBody($body, $mail_segments);
        $body .= "\n" . 'Diese Email wurde automatisch generiert.';
        $this->send($subject, $body);
        $this->clearMailData();
    }

    public function sendSIFACourseCompleted()
    {
        $this->veda_logger->debug('Sending Mail');
        $mail_segments = $this->mail_segment_repo->lookupMailSegments();
        $mail_segments_errors = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::ERROR);
        $body = '';
        $subject = '';
        if (count($mail_segments_errors) === 0) {
            $body .= 'SIFA Kurs erfolgreich importiert!' . "\n\n";
            $subject = 'ERFOLG ilVedaConnectorPlugin SIFA Kurs import';
        }
        if (count($mail_segments_errors) > 0) {
            $body .= 'Während der Aktualisierung sind Fehler aufgetreten:' . "\n\n";
            $body = $this->addSegmentMessagesToBody($body, $mail_segments_errors);
            $body .= "\n";
            $subject = 'FEHLER ilVedaConnectorPlugin SIFA Kurs import';
        }
        $body = $this->addImportInfoToBody($body, $mail_segments);
        $body .= "\n" . 'Diese Email wurde automatisch generiert.';
        $this->send($subject, $body);
        $this->clearMailData();
    }

    protected function send(string $subject, string $body) : void
    {
        $mmail = new ilMimeMail();
        $mmail->From($this->sender_factory->system());
        $mmail->Subject($subject, true);
        $mmail->To($this->veda_settings->getMailTargets());
        $mmail->Body($body);

        $this->veda_logger->debug("\n" . $body);
        $this->dumpMail($mmail);
        $mmail->Send();
    }

    protected function addSegmentMessagesToBody(string $body, ilVedaMailSegmentCollection $mail_segments) : string
    {
        foreach ($mail_segments as $mail_segment) {
            $body .= $mail_segment->getMessage() . "\n";
        }
        return $body;
    }

    protected function addImportInfoToBody(string $body, ilVedaMailSegmentCollection $mail_segments) : string
    {
        $segments_user_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::USER_UPDATED);
        $body .= 'Anzahl Aktualisierungen von Nutzerkonten: ' . count($segments_user_updated) . "\n";

        $segments_user_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::USER_IMPORTED);
        $body .= 'Anzahl neu importierter Nutzerkonten: ' . count($segments_user_updated) . "\n";

        $segments_courses_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::COURSE_UPDATED);
        $body .= 'Anzahl neu importierter Kurse: ' . count($segments_courses_updated) . "\n";

        $segments_mmbrshp_updated = $mail_segments->getMailSegmentsWithType(ilVedaMailSegmentType::MEMBERSHIP_UPDATED);
        $body .= 'Anzahl Aktualisierungen von Midgliedschaften: ' . count($segments_mmbrshp_updated) . "\n";

        return $body;
    }

    protected function clearMailData() : void
    {
        $this->mail_segment_repo->deleteAllMailSegments();
    }

    protected function dumpMail(
        ilMimeMail $mail
    ) : void {
        $this->veda_logger->debug('From:' . $mail->getFrom()->getFromAddress());
        $this->veda_logger->debug('Subject:' . $mail->getSubject());
        $this->veda_logger->debug('To: ' . implode(',', $mail->getTo()));
        $this->veda_logger->debug('Body:' . $mail->getFinalBody());
    }
}
