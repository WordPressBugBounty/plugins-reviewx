function formatNumber(n) {
    if (n < 1) return n.toString()
    if (n < 1e3) return n.toString()
    if (n >= 1e3 && n < 1e6) return +(n / 1e3).toFixed(1) + 'K'
    if (n >= 1e6 && n < 1e9) return +(n / 1e6).toFixed(1) + 'M'
    if (n >= 1e9 && n < 1e12) return +(n / 1e9).toFixed(1) + 'B'
    if (n >= 1e12) return +(n / 1e12).toFixed(1) + 'T'
    return n.toString()
}
function truncateString(str = '', num) {
    if (str?.length <= num) {
        return str
    }
    return str?.slice(0, num) + '...'
}
function truncateReviewerName({haveVerifiedBadge = false, showCountryFlg = false, str = ''}) {
    let showStrChar
    if(haveVerifiedBadge && showCountryFlg){
        showStrChar = 10
    } else  if(haveVerifiedBadge && !showCountryFlg){
        showStrChar = 10
    } else if(!haveVerifiedBadge && showCountryFlg){
        showStrChar = 7
    }else {
        showStrChar = 17
    }
    return truncateString(str, showStrChar)
}
function censorReviewerName({isCensor = false, str, firstChars = 1, lastChars = 1}) {
    if(isCensor){
        if (str.length <= firstChars + lastChars) return str; // If name is too short, return it as is
        const firstPart = str.slice(0, firstChars);
        const lastPart = str.slice(-lastChars);
        const middle = '*'.repeat(str.length - firstChars - lastChars);

        return `${firstPart}${middle}${lastPart}`;
    }
    return str
}
function showDisplayReviewerName({haveVerifiedBadge = false, showCountryFlg = false, str = '', isCensor = false}) {
    let value = ''
    if(isCensor){
        value = censorReviewerName({isCensor, str})
    }else {
        value = str
    }
    return truncateReviewerName({ haveVerifiedBadge, showCountryFlg, str: value });
}


function roundToNearestHalf(reviewAverage) {
    const decimal = reviewAverage % 1;
    if (decimal < 0.25) {
        return Math.floor(reviewAverage);
    } else if (decimal >= 0.25 && decimal < 0.75) {
        return Math.floor(reviewAverage) + 0.5;
    } else {
        return Math.ceil(reviewAverage);
    }
}
function isFullStar(star, reviewAverage) {
    // return star <= Math.floor(reviewAverage);
    const roundedAverage = this.roundToNearestHalf(reviewAverage);
    return star <= Math.floor(roundedAverage);
}
function isHalfStar(star, reviewAverage) {
    // return star === Math.floor(reviewAverage) + 1 && (reviewAverage % 1 === 0.5);
    const roundedAverage = this.roundToNearestHalf(reviewAverage);
    return star === Math.ceil(roundedAverage) && roundedAverage % 1 === 0.5;
}
function isFullEmptyStar(star, reviewAverage) {
    // return star > Math.floor(reviewAverage) + (reviewAverage % 1 === 0.5 ? 1 : 0);
    const roundedAverage = this.roundToNearestHalf(reviewAverage);
    return star > Math.ceil(roundedAverage);
}

function isHaveMultipleAttachment(attachmentLength, maxAttachment) {
    return attachmentLength > maxAttachment
}

function getFileExtension(url) {
    try {
        const parsedUrl = new URL(url);
        const pathname = parsedUrl.pathname;
        const extension = pathname.substring(pathname.lastIndexOf('.') + 1);
        return extension ? extension : '';
    } catch (error) {
        console.error('Invalid URL:', error);
        return '';
    }
}

function getFileType(url) {
    // Define sets of video, audio, and image extensions
    const videoExtensions = new Set(['mp4', 'mov', 'avi', 'mkv', 'webm']);
    const audioExtensions = new Set(['mp3', 'wav', 'aac', 'flac', 'ogg']);
    const imageExtensions = new Set(['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']);

    // Get the file extension
    const extension = getFileExtension(url).toLowerCase();

    // Return an object indicating the type of file
    return {
        isVideo: videoExtensions.has(extension),
        isAudio: audioExtensions.has(extension),
        isImage: imageExtensions.has(extension)
    };
}


function __rvxTooltipComponent__() {
    return {
        tooltipVisible: false,
        tooltipArrow: true,
        tooltipPosition: 'bottom',

        initial(){
            this.$nextTick(() => {
                const content = this.$refs.content;

                // Ensure the event listeners are attached after the DOM is ready
                content.addEventListener('mouseenter', () => {
                    this.tooltipVisible = true;
                });

                content.addEventListener('mouseleave', () => {
                    this.tooltipVisible = false;
                });
            });
            // this.$refs.content.addEventListener('mouseenter', () => {
            //     console.log('mouseenter =====')
            //     this.tooltipVisible = true;
            // });
            //
            // this.$refs.content.addEventListener('mouseleave', () => {
            //     console.log('mouseleave ====',)
            //     this.tooltipVisible = false;
            // });
        }
    }
}