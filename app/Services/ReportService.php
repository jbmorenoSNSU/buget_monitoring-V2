<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Unified service delegating specialized reporting requests to ChartReportService or StatementReportService.
 */
class ReportService
{
    /**
     * Create a new ReportService instance.
     */
    public function __construct(
        private ChartReportService $chartReportService,
        private StatementReportService $statementReportService
    ) {}

    /**
     * Delegate: Monthly income vs expense summary.
     */
    public function income_vs_expense(?string $from = null, ?string $to = null, ?int $person_id = null): array
    {
        return $this->chartReportService->income_vs_expense($from, $to, $person_id);
    }

    /**
     * Delegate: Category expense aggregates.
     */
    public function category_expense(int $month, int $year, ?int $person_id = null): array
    {
        return $this->chartReportService->category_expense($month, $year, $person_id);
    }

    /**
     * Delegate: Rollup statement for a specific financial account.
     */
    public function account_statement(int $account_id, string $from, string $to): array
    {
        return $this->statementReportService->account_statement($account_id, $from, $to);
    }

    /**
     * Delegate: Variance analysis on budget goals vs actual spending.
     */
    public function budget_goal_report(int $month, int $year, ?int $person_id = null): array
    {
        return $this->statementReportService->budget_goal_report($month, $year, $person_id);
    }

    /**
     * Delegate: Helper to get income vs expense for the last 6 months.
     */
    public function last_6_months_chart(?int $person_id = null): array
    {
        return $this->chartReportService->last_6_months_chart($person_id);
    }

    /**
     * Delegate: Daily spending trend.
     */
    public function daily_spending_trend(int $month, int $year, ?int $person_id = null): array
    {
        return $this->chartReportService->daily_spending_trend($month, $year, $person_id);
    }

    /**
     * Delegate: Weekly spending trend.
     */
    public function weekly_spending_trend(?int $person_id = null): array
    {
        return $this->chartReportService->weekly_spending_trend($person_id);
    }

    /**
     * Delegate: Yearly spending trend.
     */
    public function yearly_spending_trend(int $year, ?int $person_id = null): array
    {
        return $this->chartReportService->yearly_spending_trend($year, $person_id);
    }

    /**
     * Delegate: Calendar day aggregation list.
     */
    public function calendar_report(int $month, int $year, ?int $person_id = null, ?int $account_id = null): array
    {
        return $this->statementReportService->calendar_report($month, $year, $person_id, $account_id);
    }

    /**
     * Delegate: Settlement report.
     */
    public function settlement_report(string $from, string $to): array
    {
        return $this->statementReportService->settlement_report($from, $to);
    }

    /**
     * Delegate: Year in Review report.
     */
    public function year_in_review(int $year): array
    {
        return $this->chartReportService->year_in_review($year);
    }

    /**
     * Delegate: 180-day daily cashflow balance projections.
     */
    public function cashflow_projection(): array
    {
        return $this->chartReportService->cashflow_projection();
    }
}
