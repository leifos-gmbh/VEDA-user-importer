<?php

class ilVedaMailSegmentRepository implements ilVedaMailSegmentRepositoryInterface
{
    protected const TABLE_NAME = 'cron_crnhk_vedaimp_ml';
    protected ilDBInterface $il_db;
    protected ilLogger $veda_logger;

    public function __construct(
        ilDBInterface $il_db,
        ilLogger $veda_logger
    ) {
        $this->il_db = $il_db;
        $this->veda_logger = $veda_logger;
    }

    public function lookupMailSegments(): ilVedaMailSegmentCollection
    {
        $this->veda_logger->debug('Looking up mail segments.');

        $query = 'SELECT * FROM ' . self::TABLE_NAME;
        $results = $this->il_db->query($query);
        $mail_segments = [];
        while ($row = $this->il_db->fetchAssoc($results)) {
            $mail_segments[] = new ilVedaMailSegment(
                (int) $row['id'],
                $row['msg'],
                $row['type'],
                new DateTimeImmutable($row['modified'], new DateTimeZone('Utc'))
            );
        }
        return new ilVedaMailSegmentCollection($mail_segments);
    }

    public function addMailSegment(ilVedaMailSegmentInterface $mail_segment): void
    {
        $id = $this->il_db->nextId(self::TABLE_NAME);
        $date_time_immutable = new DateTimeImmutable('now', new DateTimeZone('Utc'));
        $values = [
            'id' => [
                ilDBConstants::T_INTEGER,
                $id
            ],
            'msg' => [
                ilDBConstants::T_TEXT,
                $mail_segment->getMessage()
            ],
            'type' => [
                ilDBConstants::T_TEXT,
                $mail_segment->getType()
            ],
            'modified' => [
                ilDBConstants::T_TIMESTAMP,
                $date_time_immutable->format('Y-m-d H:i:s')
            ]
        ];
        $this->il_db->insert(self::TABLE_NAME, $values);
    }

    public function deleteMailSegment(ilVedaMailSegmentInterface $mail_segment): void
    {
        $this->veda_logger->debug('Deleting mail segment.');
        $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE '
            . 'id = ' . $this->il_db->quote($mail_segment->getID(), ilDBConstants::T_INTEGER);
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }

    public function deleteAllMailSegments(): void
    {
        $this->veda_logger->debug('Deleteing all mail segments');
        $query = 'DELETE FROM ' . self::TABLE_NAME;
        $this->veda_logger->debug($query);
        $this->il_db->manipulate($query);
    }
}