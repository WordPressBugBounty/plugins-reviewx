export interface MultiCriteriaStats {
    [key: string]: number;
}
export interface MultiCriterias {
    key: string;
    name: string;
}
export interface CriteriaProps {
    criteriaStats: MultiCriteriaStats;
    criterias: MultiCriterias[];
    showPercent?: boolean;
    showStar?: boolean;
    starColor?: string;
    starSize?: string;
    height?: number;
    background?: string;
    existingRating?: number;
}
