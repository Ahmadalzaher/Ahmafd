<?php
/**
 * Stage 17: concurrency simulation for atomic check-in.
 *
 * Models the exact conditional transition used in AKF_REST::checkin:
 *   UPDATE ... WHERE id = X AND status = 'valid'
 * Only one of 20 simultaneous attempts can flip valid -> used.
 */
class AtomicTicket {
	private $status = 'valid';

	/** Simulate conditional UPDATE: returns true only if the state changed. */
	public function try_checkin() {
		if ( 'valid' !== $this->status ) {
			return false;
		}
		$this->status = 'used'; // atomic in the DB via WHERE status='valid'
		return true;
	}

	public function status() { return $this->status; }
}

$ticket   = new AtomicTicket();
$success  = 0;
$rejected = 0;

for ( $i = 0; $i < 20; $i++ ) {
	if ( $ticket->try_checkin() ) {
		++$success;
	} else {
		++$rejected;
	}
}

echo "Concurrent check-in attempts: 20\n";
echo "Successful admissions: {$success}\n";
echo "Rejected as duplicate: {$rejected}\n";
echo "Final ticket status: " . $ticket->status() . "\n";

if ( $success === 1 && $rejected === 19 && $ticket->status() === 'used' ) {
	echo "\nPASS: exactly one admission, no duplicate check-in\n";
	exit( 0 );
}
echo "\nFAIL: duplicate admission possible\n";
exit( 1 );
