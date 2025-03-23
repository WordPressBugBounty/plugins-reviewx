function __reviewXState__() {
    return {
        rvxAttributes: {
            // settings: {
            //     isVerified: 'no',
            // },
            product: {
                id: null,
                postType: 'product',
            },
            userInfo: {
                isLoggedIn: null,
                id: null,
                name: null,
                email: null,
                isVerified: false,
            },
            baseDomain: '',
            formLevelData: {}
        },
        // baseURL: `${location.origin}/wp-json/reviewx/api/v1/storefront`,
        newReview: {
            rating: 5,
            reviewer_email: '',
            reviewer_name: '',
            is_anonymous: false,
            consent: false,
            title: '',
            feedback: '',
            is_recommended: 'true'
        },
        fetchReviewsIsLoading: true,
        isReviewSubmitting: false,
        fetchReviewsSettingsIsLoading: false,
        haveReviews: false,
        isFiltering: false,
        reviewsData: null,
        reviewListShortCodeData: null,
        reviewWithReviewIdsShortCodeData: null,
        reviewSettingsData: undefined,
        reviewAggregationData: null,
        isLoadMoreReviews: false,
        layoutView: 'list',
        formatAggregations: [],
        formatMultiCriteriaAggregations: [],
        multiCriteriaRatings: [],
        attachmentFiles: [],
        showReviewSuccessModal: false,
        showReviewDetailsModal: false,
        selectedReviewDetails: {},
        storeFrontReviewQuery: {},
        storeFrontReviewListShortcodeQuery: {},
        storeFrontValidation: {
            isValidReviewTitle: false,
            isValidReviewFeedback: false,
            isValidReviewerName: false,
            isValidReviewerEmail: false,
            isNotRobot: false,
            reviewSubmitFailed: false,
            isAcceptConsent: false
        },

        rvxAttributesDataSetHandler(rvxAttributes) {
            this.rvxAttributes = {
                ...this.rvxAttributes,
                ...rvxAttributes,
                baseDomain:`${rvxAttributes.domain.baseDomain}/wp-json/reviewx/api/v1/storefront`
            }
        },
        get isUserLoggedIn() {
            return Number(this.rvxAttributes.userInfo?.isLoggedIn) === 1
        },
        get isUserVerified() {
            return this.rvxAttributes.userInfo?.isVerified
        },
        get haveAddReviewAbility() {
            if (this.reviewSettingsData?.data.setting.review_settings.reviews.review_submission_policy.options.anyone) {
                return true
            }
            return this.reviewSettingsData?.data.setting.review_settings.reviews.review_submission_policy.options.verified_customer && this.isUserLoggedIn && this.isUserVerified;
        },
        get haveDisplayBadgeShowAbility() {
            return !!(this.reviewSettingsData?.data?.setting?.widget_settings.display_badges && Number(this.reviewAggregationData?.data?.aggregation?.total_avg_review >= 4));
        },
        selectedReviewDetailsHandler(review) {
            this.selectedReviewDetails = review
            this.showReviewDetailsModal = true
        },
        appendReviews(newRes) {
            this.reviewsData = {
                ...this.reviewsData,
                data: {
                    ...this.reviewsData.data,
                    reviews: [
                        ...this.reviewsData.data.reviews,
                        ...newRes.data.reviews],
                    meta: {...this.reviewsData.data.meta, ...newRes.data.meta},
                }
            };
        },
        async fetchReviews({query, loadMoreReview, productId}) {
            if(loadMoreReview){
                this.isLoadMoreReviews = true
            }else{
                this.fetchReviewsIsLoading = true;
            }
            let queryParams;
            if (query) {
                const newQuery = {
                    ...query
                }
                queryParams = this.generateQueryParams(newQuery)
            }
            try {
                const url = `${this.rvxAttributes.baseDomain}/${productId}/reviews${queryParams ? '?' + queryParams : ''}`;
                const data = await fetch(url);
                const res = await data.json();
                if (loadMoreReview && this.reviewsData?.data?.reviews?.length) {
                    this.appendReviews(res);
                } else {
                    this.reviewsData = res;
                    this.reviewListShortCodeData = res;
                }

                if (res?.data?.reviews?.length) {
                    this.haveReviews = true;
                }
            } catch (e) {
                console.log('Error fetching reviews:', e);
            } finally {
                this.fetchReviewsIsLoading = false;
                this.isLoadMoreReviews = false
            }
        },
        async fetchReviewListShortCodes(ids) {
            this.fetchReviewsIsLoading = true;
            const reviewListShortCodeIds = ids?.[0]
            if (!reviewListShortCodeIds) return
            try {
                const url = `${this.rvxAttributes.baseDomain}/${reviewListShortCodeIds}/reviews`;
                const data = await fetch(url);
                const res = await data.json();
                if (!res?.data?.reviews?.length) {
                    this.haveReviews = true; // No more reviews available
                } else {
                    this.reviewListShortCodeData = res;
                }
            } catch (e) {
                console.log('Error fetching reviews:', e);
            } finally {
                this.fetchReviewsIsLoading = false;
            }
        },
        async fetchReviewListWithIdsShortCodes(ids) {
            this.fetchReviewsIsLoading = true;
            const reviewsShortCodeIds = ids
            if (!reviewsShortCodeIds?.length) return
            const payload = {
                review_ids: reviewsShortCodeIds
            }
            try {
                const url = `${this.rvxAttributes.baseDomain}/widgets/short/code/reviews`;
                const data = await fetch(url, {
                    method: 'POST',
                    body: JSON.stringify(payload),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const res = await data.json();
                // console.log('res =====', res)
                if (!res?.data?.reviews?.length) {
                    this.haveReviews = true; // No more reviews available
                } else {
                    this.reviewWithReviewIdsShortCodeData = res;
                }
            } catch (e) {
                console.log('Error fetching reviews:', e);
            } finally {
                this.fetchReviewsIsLoading = false;
            }
        },
        loadMoreReviewHandler() {
            
            const cursor = this.reviewsData?.data?.meta?.next_cursor;
            if (cursor) {
                this.storeFrontReviewQuery = {
                    ...this.storeFrontReviewQuery,
                    cursor
                };
                this.fetchReviews({
                    query: this.storeFrontReviewQuery,
                    loadMoreReview: true,
                    productId: this.rvxAttributes?.product?.id
                });
            }
        },
        async fetchReviewListShortcodeReviews({ query, loadMoreReview, productId }) {
            if (loadMoreReview) {
                this.isLoadMoreReviews = true;
            } else {
                this.fetchReviewsIsLoading = true;
            }
            let queryParams;
            if (query) {
                const newQuery = { ...query };
                queryParams = this.generateQueryParams(newQuery);
            }
            try {
                const url = `${this.rvxAttributes.baseDomain}/${productId}/reviews${queryParams ? '?' + queryParams : ''}`;
                const data = await fetch(url);
                const res = await data.json();
                
                if (loadMoreReview) {
                    // Append new reviews to existing data
                    if (this.reviewListShortCodeData?.data?.reviews) {
                        this.reviewListShortCodeData = {
                            ...this.reviewListShortCodeData,
                            data: {
                                ...this.reviewListShortCodeData.data,
                                reviews: [...this.reviewListShortCodeData.data.reviews, ...res.data.reviews],
                                meta: res.data.meta // Update next_cursor
                            }
                        };
                    } else {
                        this.reviewListShortCodeData = res;
                    }
                } else {
                    // Initial load
                    this.reviewListShortCodeData = res;
                }
        
                if (res?.data?.reviews?.length) {
                    this.haveReviews = true;
                }
            } catch (e) {
                console.log('Error fetching reviews:', e);
            } finally {
                this.fetchReviewsIsLoading = false;
                this.isLoadMoreReviews = false;
            }
        },
        loadMoreReviewListShortcodeHandler() {
            const cursor = this.reviewListShortCodeData?.data?.meta?.next_cursor;
            if (cursor) {
                this.storeFrontReviewListShortcodeQuery = {
                    ...this.storeFrontReviewListShortcodeQuery,
                    cursor
                };
                this.fetchReviewListShortcodeReviews({
                    query: this.storeFrontReviewListShortcodeQuery,
                    loadMoreReview: true,
                    productId: this.reviewListShortCodeData?.data?.reviews[0]?.product_wp_id
                });
                console.log('Product ID: ', storeFrontReviewListShortcodeQuery);
            }
        },
        async fetchReviewsSettings() {
            this.fetchReviewsSettingsIsLoading = true;
            let postType = this.rvxAttributes?.product?.postType ? this.rvxAttributes?.product?.postType : 'product';
            try {
                const data = await fetch(`${this.rvxAttributes.baseDomain}/wp/settings${'?cpt_type=' + postType}`);
                const res = await data.json();
                this.reviewSettingsData = res;
                this.layoutView = res?.data.setting?.widget_settings?.outline?.layout_type
                // console.log('Settings:', res);
                if (res?.data?.setting?.review_settings) {
                    this.multiCriteriaRatings = this.createMultiCriteriaRatingObject({
                        criterias: res?.data.setting.review_settings.reviews.multicriteria.criterias
                    })
                }

                if (res.data.setting.review_settings?.reviews?.recaptcha?.enabled) {
                    const script = document.createElement('script');
                    script.src = `https://www.google.com/recaptcha/api.js?render=${res.data.setting.review_settings?.reviews?.recaptcha.site_key}`
                    document.head.appendChild(script);
                }
            } catch (e) {
                console.log('Error fetching settings:', e);
            } finally {
                this.fetchReviewsSettingsIsLoading = false;
            }
        },
        async fetchReviewsAggregation({productId}) {
            // console.log('Fetching review Aggregation');
            this.fetchReviewsSettingsIsLoading = true;
            try {
                const data = await fetch(`${this.rvxAttributes.baseDomain}/${productId}/insight`);
                const res = await data.json();
                if (res.data?.aggregation) {
                    this.formatAggregations = [
                        {
                            rating: 5,
                            total: res?.data?.aggregation.five.total,
                            percent: res?.data.aggregation.five.percentage
                        },
                        {
                            rating: 4,
                            total: res?.data?.aggregation.four.total,
                            percent: res?.data.aggregation.four.percentage
                        },
                        {
                            rating: 3,
                            total: res?.data?.aggregation.three.total,
                            percent: res?.data.aggregation.three.percentage
                        },
                        {
                            rating: 2,
                            total: res?.data?.aggregation.two.total,
                            percent: res?.data.aggregation.two.percentage
                        },
                        {
                            rating: 1,
                            total: res?.data?.aggregation.one.total,
                            percent: res?.data.aggregation.one.percentage
                        },
                    ]
                    this.formatMultiCriteriaAggregations = this.formattedMultiCriteriaAggregation({
                        criteriaStats: res.data.criteria_stats,
                        criterias: this.reviewSettingsData.data.setting.review_settings.reviews.multicriteria.criterias,
                        totalAvaReview: res.data?.aggregation.total_avg_review
                    })
                }
                this.reviewAggregationData = res;

            } catch (e) {
                console.log('Error fetching settings:', e);
            } finally {
                this.fetchReviewsSettingsIsLoading = false;
            }
        },
        formattedMultiCriteriaAggregation({criteriaStats, criterias, totalAvaReview, percentDefaultValue = 5}) {
            return criterias?.map((criteria) => {
                const total = criteriaStats[criteria.key] || 0;
                let modifyTotal
                if (total === 0 && totalAvaReview > 0) {
                    modifyTotal = totalAvaReview
                } else {
                    modifyTotal = total
                }
                const percent = (modifyTotal / percentDefaultValue) * 100;
                return {
                    key: criteria.key,
                    name: criteria.name,
                    total: modifyTotal,
                    // total,
                    percent,
                };
            });
        },
        createMultiCriteriaRatingObject({criterias, criteriaStats, existingRating}) {
            const newCriterial = criterias.map((criteria) => {
                if (criteriaStats) {
                    let rating;
                    const getRating = criteriaStats?.[criteria.key];
                    if (getRating) {
                        rating = getRating;
                    } else if (existingRating) {
                        rating = Number(existingRating);
                    } else {
                        rating = 5
                    }
                    return {
                        ...criteria,
                        rating,
                    };
                } else {
                    return {
                        ...criteria,
                        rating: 5,
                    };
                }
            });
            return newCriterial;
        },
        notifyAttachmentAdded(files) {
            this.attachmentFiles = files.blobFiles
        },
        multiCriteriaRatingsToObject(array) {
            return array.reduce((acc, curr) => {
                acc[curr.key] = curr.rating;
                return acc;
            }, {});
        },
        googleRecaptchaVerify() {
            return new Promise((resolve, reject) => {
                const siteKey = this.reviewSettingsData?.data?.setting.review_settings?.reviews?.recaptcha.site_key;
                if (!siteKey || !grecaptcha) {
                    return reject('Recaptcha or site key is not defined');
                }
                const baseAPIEndPoint = this.rvxAttributes.baseDomain
                grecaptcha.ready(function () {
                    grecaptcha.execute(siteKey, {action: 'submit'}).then(async function (token) {
                        try {
                            const payload = {
                                token
                            }
                            const data = await fetch(`${baseAPIEndPoint}/google/recaptcha/verify`, {
                                method: 'post',
                                body: JSON.stringify(payload),
                                headers: {
                                    'Content-Type': 'application/json',
                                }
                            })
                            const res = await data.json()
                            resolve(res.result);
                        } catch (e) {
                            reject(e);
                        }
                    }).catch(function (error) {
                        console.error('Recaptcha error:', error);
                        reject(error);
                    });
                });
            });
        },
        checkValidity(field) {
            this.storeFrontValidation[field] = !!this.newReview[field];
        },
        async fetchUserIPAddress() {
            try {
                const response = await fetch('https://api.ipify.org?format=json');
                const data = await response.json();
                console.log('Your IP address is:', data.ip);
                return data.ip;
            } catch (error) {
                console.error('Error fetching IP address:', error);
                return null; // Handle error as needed
            }
        },
        async reviewFormValidation() {
            let isValid = true;
            const allowReviewTitle = this.reviewSettingsData?.data?.setting?.review_settings.reviews.allow_review_titles
            this.storeFrontValidation = {
                isValidReviewTitle: false,
                isValidReviewFeedback: false,
                isValidReviewerName: false,
                isValidReviewerEmail: false,
                isNotRobot: false,
                reviewSubmitFailed: false,
                isAcceptConsent: false
            }

            if (this.reviewSettingsData?.data?.setting.review_settings?.reviews?.recaptcha?.enabled) {
                const res = await this.googleRecaptchaVerify()
                this.storeFrontValidation.isNotRobot = res.result;
                isValid = !res.result
            }
            if (this.reviewSettingsData?.data?.setting.review_settings?.reviews?.show_consent_checkbox?.enabled && !this.newReview.consent) {
                this.storeFrontValidation.isAcceptConsent = true;
                isValid = false;
            }

            if (!this.isUserLoggedIn) {
                if (allowReviewTitle && !this.newReview.title) {
                    this.storeFrontValidation.isValidReviewTitle = true;

                    isValid = false;
                }
                if (!this.newReview.feedback) {
                    this.storeFrontValidation.isValidReviewFeedback = true;
                    isValid = false;
                }

                if (!this.newReview.reviewer_name) {
                    this.storeFrontValidation.isValidReviewerName = true;
                    isValid = false;
                }
                if (!this.newReview.reviewer_email) {
                    this.storeFrontValidation.isValidReviewerEmail = true;
                    isValid = false;
                }
            } else {
                if (allowReviewTitle && !this.newReview.title) {
                    this.storeFrontValidation.isValidReviewTitle = true;
                    isValid = false;
                }
                if (!this.newReview.feedback) {
                    this.storeFrontValidation.isValidReviewFeedback = true;
                    isValid = false;
                }
            }

            return isValid
        },
        async reviewSubmitHandler() {
            const result = await this.reviewFormValidation()
            console.log('validate result ===========', result)
            const userIP = await this.fetchUserIPAddress()
            if (!result) return
            this.isReviewSubmitting = true
            const newCriterias = this.multiCriteriaRatingsToObject(this.multiCriteriaRatings)
            const updatedReviewPayload = {
                feedback: this.newReview.feedback,
                reviewer_name: this.isUserLoggedIn ? this.rvxAttributes.userInfo?.name : this.newReview.reviewer_name,
                reviewer_email: this.isUserLoggedIn ? this.rvxAttributes.userInfo?.email : this.newReview.reviewer_email,
                user_id: this.isUserLoggedIn ? this.rvxAttributes.userInfo?.id : 0,
                criterias: newCriterias,
                rating: this.newReview.rating,
                is_anonymous: this.newReview.is_anonymous,
                wp_post_id: this.reviewAggregationData?.data?.product?.wp_id,
                ip: userIP ?? '0.0.0.0'
            }
            if (this.reviewSettingsData?.data?.setting?.review_settings.reviews.allow_recommendations) {
                updatedReviewPayload.is_recommended = this.newReview.is_recommended;
            }

            if (this.reviewSettingsData?.data?.setting?.review_settings.reviews.allow_review_titles) {
                updatedReviewPayload.title = this.newReview.title;
            }

            if (this.reviewSettingsData?.data?.setting?.review_settings.reviews.photo_reviews_allowed && this.attachmentFiles.length) {
                updatedReviewPayload.attachments = this.attachmentFiles;
            }

            const payload = this.generateFormData({objectData: updatedReviewPayload})
            // payload.forEach((value, key) => {
            //     if (value instanceof File) {
            //         console.log(
            //             `${key}: [File] Name: ${value.name}, Size: ${value.size}, Type: ${value.type}`
            //         );
            //     } else {
            //         console.log(`${key}: ${value}`);
            //     }
            // });
            try {
                const data = await fetch(`${this.rvxAttributes.baseDomain}/reviews`, {
                    method: 'post',
                    body: payload,
                })
                const res = await data.json()
                // console.log('res =====', res.code)
                // if(res.code)
                this.notifyReviewAdded()
                this.newReview = {
                    rating: 5,
                    reviewer_email: '',
                    reviewer_name: '',
                    is_anonymous: false,
                    consent: false,
                    title: '',
                    feedback: '',
                    is_recommended: 'true'
                }
                this.showReviewSuccessModal = true
                document.getElementById('rvx-storefront-widget').scrollIntoView({behavior: 'smooth'});
                if(this.reviewSettingsData?.data?.setting?.review_settings?.reviews?.auto_approve_reviews){
                    await this.fetchReviews({productId: this.rvxAttributes?.product?.id});
                }
            } catch (error) {
                console.log('error ======', error);
            } finally {
                this.isReviewSubmitting = false
            }
        },
        notifyReviewAdded() {
            this.$dispatch('notify-review-added', {message: 'Success'});
        },
        generateQueryParams(params) {
            const queryParams = new URLSearchParams();
            for (const key in params) {
                if (params.hasOwnProperty(key)) {
                    let value = params[key];
                    // Handle array values by appending multiple times with the same key
                    if (Array.isArray(value)) {
                        value.forEach((item) => queryParams.append(key, item));
                    } else if (value !== null && value !== undefined) {
                        // Only append non-null and non-undefined values
                        queryParams.append(key, value);
                    }
                }
            }
            return queryParams.toString();
        },
        isObject(value) {
            return (
                typeof value === 'object' &&
                value !== null &&
                !Array.isArray(value) &&
                !(value instanceof File)
            );
        },
        avatarComponent({src = '', alt = 'Default Name'}) {
            return {
                hasError: false,
                src: src,
                alt: alt,
                showImage() {
                    return this.src && !this.hasError;
                },
                onError() {
                    this.hasError = true;
                }
            };
        },
        copyClipboardComponent() {
            return {
                copySuccess: false,
                copyClipboard(input) {
                    navigator.clipboard.writeText(input)
                        .then(() => {
                            this.copySuccess = true;
                            setTimeout(() => this.copySuccess = false, 1000);
                        })
                        .catch(err => {
                            console.error('Failed to copy: ', err);
                        });
                }
            };
        },
        reviewHelpInfoComponent() {
            return {
                async likeDislikeHandler({preference, uid}) {
                    if (!this.isUserLoggedIn) {
                        this.showErrorToastMessage = true
                        setTimeout(() => this.showErrorToastMessage = false, 1000);
                        throw new Error('Please login first')
                    }
                    const payload = {
                        preference,
                    };
                    try {
                        const res = await fetch(`${this.rvxAttributes.baseDomain}/reviews/${uid}/preference`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(payload)
                        });
                        // console.log('res ======', res)
                        // this.$dispatch('notify-like-dislike')
                    } catch (error) {
                        console.log('error =======', error);
                        // toast.error(error.response?.data?.message);
                    }
                }
            }
        },
        generateFormData({objectData, fileKey}) {
            const formData = new FormData();
            const appendFormData = (key, value) => {
                if (value instanceof File && fileKey) {
                    formData.append(fileKey, value);
                } else if (value instanceof File) {
                    formData.append(key, value);
                } else if (this.isObject(value)) {
                    for (const subKey in value) {
                        if (Object.prototype.hasOwnProperty.call(value, subKey)) {
                            appendFormData(`${key}[${subKey}]`, value[subKey]);
                        }
                    }
                } else if (Array.isArray(value)) {
                    value.forEach((item, index) => {
                        if (item instanceof File) {
                            const fileArrayKey = fileKey
                                ? `${fileKey}[${index}]`
                                : `${key}[${index}]`;
                            formData.append(fileArrayKey, item);
                        } else if (this.isObject(item)) {
                            for (const subKey in item) {
                                if (Object.prototype.hasOwnProperty.call(item, subKey)) {
                                    appendFormData(`${key}[${index}][${subKey}]`, item[subKey]);
                                }
                            }
                        } else {
                            formData.append(`${key}[${index}]`, item);
                        }
                    });
                } else {
                    formData.append(key, value);
                }
            };

            for (const key in objectData) {
                if (Object.prototype.hasOwnProperty.call(objectData, key)) {
                    const value = objectData[key];
                    appendFormData(key, value);
                }
            }

            return formData;
        },

        async initialize(data) {
            const parseAttData = JSON.parse(data.data)
            const parseFormLabels = JSON.parse(data.formLevelData)
            const concatValue = {
                ...parseAttData,
                formLevelData: {
                    ...parseFormLabels
                }
            }
            this.rvxAttributesDataSetHandler(concatValue)
            try {
                // console.log('init 1')
                await this.fetchReviewsSettings();
                if (this.rvxAttributes?.product?.id) {
                    await this.fetchReviewsAggregation({productId: this.rvxAttributes?.product?.id});
                    await this.fetchReviews({productId: this.rvxAttributes?.product?.id});
                }
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },

        async initializeMyAccountReviewForm(data) {
            // const parseData = JSON.parse(data.data)
            // console.log('init 2')
            const parseAttData = JSON.parse(data.data)
            const parseFormLabels = JSON.parse(data?.formLevelData)
            // console.log('parseAttData', parseAttData)
            // console.log('parseFormLabels', parseFormLabels)
            const concatValue = {
                ...parseAttData,
                formLevelData: {
                    ...parseFormLabels
                }
            }
            // console.log('concatValue', concatValue)
            this.rvxAttributesDataSetHandler(concatValue)
            // this.rvxAttributesDataSetHandler(parseData)
            try {
                await this.fetchReviewsSettings();
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },
        async initializeMyAccountReviewFormOnProductChange(productId) {
            try {
                // console.log('init 3')
                await this.fetchReviewsAggregation({productId: productId});
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },
        async initializeReviewGraphShortCodes(data) {
            const parseData = JSON.parse(data.data)
            this.rvxAttributesDataSetHandler(parseData)
            // console.log('init 4')
            try {
                await this.fetchReviewsSettings();
                await this.fetchReviewsAggregation({productId: parseData.product.id});
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },
        async initializeReviewListShortCodes(data) {
            const parseData = JSON.parse(data.data)
            this.rvxAttributesDataSetHandler(parseData)
            try {
                // console.log('init 5')
                await this.fetchReviewsSettings();
                await this.fetchReviewListShortCodes(parseData.ids);
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },
        async initializeReviewWithReviewIdsShortCodes(data) {
            const parseData = JSON.parse(data.data)
            this.rvxAttributesDataSetHandler(parseData)
            try {
                // console.log('init 6')
                await this.fetchReviewsSettings();
                await this.fetchReviewListWithIdsShortCodes(parseData.ids);
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },
        async initializeReviewStatsShortCodes(data) {
            const parseData = JSON.parse(data.data)
            this.rvxAttributesDataSetHandler(parseData)
            try {
                // console.log('init 7')
                await this.fetchReviewsSettings();
                await this.fetchReviewsAggregation({productId: parseData.product.id});
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },
        async initializeReviewSummaryShortCodes(data) {
            const parseData = JSON.parse(data.data)
            this.rvxAttributesDataSetHandler(parseData)
            try {
                // console.log('init 8')
                await this.fetchReviewsSettings();
                await this.fetchReviewsAggregation({productId: parseData.product.id});
            } catch (error) {
                console.error('Error during initialization:', error);
            }
        },
    }
}