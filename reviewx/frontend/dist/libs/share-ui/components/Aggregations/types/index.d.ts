export interface Aggregations {
    total_avg_review: number;
    total_reviews: number;
    five: StartAggregations;
    four: StartAggregations;
    three: StartAggregations;
    two: StartAggregations;
    one: StartAggregations;
}
export interface StartAggregations {
    percentage: number;
    total: number;
}
