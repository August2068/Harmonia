import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

let button = document.getElementById("buttonFav");
const id = button.dataset.trackId;

checkFav();

button.addEventListener("click", (e)=>{
    fav();
});

async function checkFav(){
    try {
        const res = await fetch(`/favoriteCheck/${id}`);
        if (!res.ok){
            throw new Error(res.status);
        }
        const result = await res.json();
        result.fav?button.innerText = "Unfav":button.innerText = "fav"
    } catch (error){
        console.error(error.message);
    }
}

async function fav(){
    try {
        const res = await fetch(`/favorite/${id}`);
        if (!res.ok){
            throw new Error(res.status);
        }
        const result = await res.json();
        result.fav?button.innerText = "Unfav":button.innerText = "fav"
    } catch (error){
        console.error(error.message);
    }
}

