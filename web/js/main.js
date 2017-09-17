var slidesInterval = 5000;
var currentSlide = 0;
var slidesCount;
var dots;
var Timeout;
$(function() {//kai puslapis užsikrovė
    slidesCount = $('.slide').length;//gauname kiek išviso yra skaidrių
    ChangeSlide();//pradeda skaidrių rodymą
});

//jei buvo paspausta ant taškiuko
function JumpToSlide(index)
{
    $('.slide').eq(currentSlide).hide();//išjungiame dabartinę skaidrę
    $('.dot').eq(currentSlide).toggleClass('active');//išjungiame dabartinį taškiuką
    currentSlide = index;
    clearTimeout(Timeout);//panaikinam laikmatį, kad galėtume pradėt iš naujo
    ChangeSlide();//įjungiame naują skaidrę
}

function Iterate()
{
    $('.dot').eq(currentSlide).toggleClass('active');//išjungiame dabartinės skaidrės taškiuką
    $('.slide').eq(currentSlide).hide();//išjungiame dabartinę skaidrę
    currentSlide++;
    if(currentSlide >= slidesCount)
        currentSlide = 0;
    ChangeSlide();//pereinam į sekančią skaidrę
}

function ChangeSlide()
{
    $('.slide').eq(currentSlide).show();//parodo dabartinę (pradžioj visos skaidrės display='none') skaidrę
    $('.dot').eq(currentSlide).toggleClass('active');//taškiukui (rodo kuri skaidrė) suteikiama 'active' klasė
    Timeout = setTimeout(Iterate, slidesInterval);//po duoto laiko keičiam skaidrę
}


