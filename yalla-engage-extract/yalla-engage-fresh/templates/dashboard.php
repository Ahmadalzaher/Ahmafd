<?php
/**
 * Dashboard template for Yalla Engage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$confirmed   = isset( $confirmed ) ? $confirmed : 0;
$pending     = isset( $pending ) ? $pending : 0;
$events      = isset( $events ) ? $events : array();
$last_camp   = isset( $last_camp ) ? $last_camp : null;
$last_regs   = isset( $last_regs ) ? $last_regs : array();
$total_regs  = isset( $total_regs ) ? $total_regs : 0;
$checked_in  = isset( $checked_in ) ? $checked_in : 0;
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'النشرة والتسجيل — لوحة رئيسية', 'yalla-engage' ); ?></h1>

	<div class="yena-dashboard-cards" style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem;">
		<div style="flex:1;min-width:200px;background:#fff;border:1px solid #ddd;padding:1rem;border-radius:6px;">
			<h3 style="margin:0 0 .5rem;font-size:1rem;text-transform:uppercase;letter-spacing:.1em;color:#666;">مشتركون مؤكدون</h3>
			<p style="font-size:2rem;margin:0;color:#b9252b;"><?php echo (int) $confirmed; ?></p>
		</div>
		<div style="flex:1;min-width:200px;background:#fff;border:1px solid #ddd;padding:1rem;border-radius:6px;">
			<h3 style="margin:0 0 .5rem;font-size:1rem;text-transform:uppercase;letter-spacing:.1em;color:#666;">في انتظار التأكيد</h3>
			<p style="font-size:2rem;margin:0;color:#666;"><?php echo (int) $pending; ?></p>
		</div>
		<div style="flex:1;min-width:200px;background:#fff;border:1px solid #ddd;padding:1rem;border-radius:6px;">
			<h3 style="margin:0 0 .5rem;font-size:1rem;text-transform:uppercase;letter-spacing:.1em;color:#666;">آخر حملة</h3>
			<p style="font-size:1.2rem;margin:0;color:#222;"><?php echo $last_camp ? esc_html( $last_camp->subject ) : '—'; ?></p>
			<p style="font-size:.85rem;margin:0;color:#666;"><?php echo $last_camp ? esc_html( $last_camp->status ) : '—'; ?></p>
		</div>
		<div style="flex:1;min-width:200px;background:#fff;border:1px solid #ddd;padding:1rem;border-radius:6px;">
			<h3 style="margin:0 0 .5rem;font-size:1rem;text-transform:uppercase;letter-spacing:.1em;color:#666;">حضر / الإجمالي</h3>
			<p style="font-size:2rem;margin:0;color:#222;"><?php echo (int) $checked_in; ?> من <?php echo (int) $total_regs; ?></p>
		</div>
	</div>

	<h2><?php echo esc_html__( 'آخر التسجيلات', 'yalla-engage' ); ?></h2>
	<table class="wp-list-table widefixed fixed striped">
		<thead>
			<tr>
				<th><?php echo esc_html__( 'الاسم', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'البريد الإلكتروني', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الفعالية', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الحالة', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'التاريخ', 'yalla-engage' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $last_regs as $reg ) : ?>
				<tr>
					<td><?php echo esc_html( $reg->name ); ?></td>
					<td><?php echo esc_html( $reg->email ); ?></td>
					<td><?php echo esc_html( get_the_title( $reg->event_id ) ); ?></td>
					<td><?php echo esc_html( $reg->status ); ?></td>
					<td><?php echo esc_html( $reg->created_at ); ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if ( empty( $last_regs ) ) : ?>
				<tr><td colspan="5"><?php echo esc_html__( 'لا توجد تسجيلات بعد.', 'yalla-engage' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>

	<h2><?php echo esc_html__( 'الفعاليات القادمة', 'yalla-engage' ); ?></h2>
	<table class="wp-list-table widefixed fixed striped">
		<thead>
			<tr>
				<th><?php echo esc_html__( 'الفعالية', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'التاريخ', 'yalla-engage' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $events as $event ) : ?>
				<tr>
					<td><?php echo esc_html( $event->post_title ); ?></td>
					<td><?php echo esc_html( get_post_meta( $event->ID, '_yd_date', true ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if ( empty( $events ) ) : ?>
				<tr><td colspan="2"><?php echo esc_html__( 'لا توجد فعاليات قادمة.', 'yalla-engage' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
