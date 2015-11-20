$.ajax({
    url: 'https://apps.allrecipes.com/v1/recipe-hubs/1/recipes?page=10&pagesize=100&sorttype=Popular',
    headers: {
        Authorization: 'Bearer P6k/U+2F1ECWIwpmI527pSQ30GNObHZZLO7RXuEdx8mgPACA054WqOOZtWW6xOz/jaI02X5Vsae8GiWvxoulBGzoH0xl+zrnE69UxRMQKgQPVoB0+g9DqYhWmy5SIK+t',
        Origin: 'http://allrecipes.com',
        Referer: 'http://allrecipes.com/recipes/?internalSource=hub%20nav&referringId=76&referringContentType=recipe%20hub&referringPosition=2&linkName=hub%20nav%20exposed&clickId=hub%20nav%202&page=9',
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36',
        'X-Requested-With': 'XMLHttpRequest'
    },
    success: function (response) {
        console.log(response);
    }
});
