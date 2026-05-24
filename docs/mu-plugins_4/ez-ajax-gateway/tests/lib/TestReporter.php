<?php

declare(strict_types=1);

/**
 * Collects pass/fail/warn/info rows and emits human + JSON output.
 */
final class TestReporter
{
	/** @var list<array{id:string, status:string, message:string, group:string}> */
	private array $rows = [];

	public function pass( string $id, string $message, string $group = 'general' ): void
	{
		$this->rows[] = [
			'id'      => $id,
			'status'  => 'pass',
			'message' => $message,
			'group'   => $group,
		];
	}

	public function fail( string $id, string $message, string $group = 'general' ): void
	{
		$this->rows[] = [
			'id'      => $id,
			'status'  => 'fail',
			'message' => $message,
			'group'   => $group,
		];
	}

	public function warn( string $id, string $message, string $group = 'general' ): void
	{
		$this->rows[] = [
			'id'      => $id,
			'status'  => 'warn',
			'message' => $message,
			'group'   => $group,
		];
	}

	public function info( string $id, string $message, string $group = 'general' ): void
	{
		$this->rows[] = [
			'id'      => $id,
			'status'  => 'info',
			'message' => $message,
			'group'   => $group,
		];
	}

	public function hasFailures(): bool
	{
		foreach ( $this->rows as $row ) {
			if ( 'fail' === $row['status'] ) {
				return true;
			}
		}

		return false;
	}

	public function hasWarnings(): bool
	{
		foreach ( $this->rows as $row ) {
			if ( 'warn' === $row['status'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array{summary: array<string, int>, checks: list<array{id:string, status:string, message:string, group:string}>}
	 */
	public function toArray(): array
	{
		$summary = [
			'pass'  => 0,
			'fail'  => 0,
			'warn'  => 0,
			'info'  => 0,
			'total' => count( $this->rows ),
		];
		foreach ( $this->rows as $row ) {
			if ( isset( $summary[ $row['status'] ] ) ) {
				++$summary[ $row['status'] ];
			}
		}

		return [
			'summary' => $summary,
			'checks'  => $this->rows,
		];
	}

	public function printTable( bool $json_only ): void
	{
		if ( $json_only ) {
			return;
		}

		$w_id = 28;
		foreach ( $this->rows as $row ) {
			$w_id = max( $w_id, strlen( $row['id'] ) + 2 );
		}

		fwrite( STDERR, str_repeat( '-', $w_id + 52 ) . PHP_EOL );
		fwrite( STDERR, sprintf( "%-{$w_id}s %-6s %s\n", 'CHECK', 'STATUS', 'MESSAGE' ) );
		fwrite( STDERR, str_repeat( '-', $w_id + 52 ) . PHP_EOL );
		foreach ( $this->rows as $row ) {
			$label = strtoupper( $row['status'] );
			fwrite(
				STDERR,
				sprintf( "%-{$w_id}s %-6s %s\n", $row['id'], $label, $row['message'] )
			);
		}
		fwrite( STDERR, str_repeat( '-', $w_id + 52 ) . PHP_EOL );
	}

	public function printJson(): void
	{
		$payload = $this->toArray();
		$payload['ok'] = ! $this->hasFailures();
		echo json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
	}
}
