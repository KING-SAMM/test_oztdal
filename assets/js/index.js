// import Dashboard from "./views/Dashboard.js";
// import Posts from "./views/Posts.js";
// import CreatePost from "./views/CreatePost.js";
// import Settings from "./views/Settings.js";
// import SinglePost from "./views/SinglePost.js";
// import EditPost from "./views/EditPost.js";
import Register from "../../views/register-refactor2";

// Define a regex object to match url with params 
// e.g a regex object that matches, say, '/posts/:id/'
const pathToRegexp = path => new RegExp("^" + path.replace(/\//g, "\\/").replace(/:\w+/g, "(.+)") + "$");

// Get the url params form the match array
const getParams = match => {
    // Get values from the match result
    const values = match.result.slice(1);
    // Get param keys from the route path 
    // (capture group is used to match multiple param paths, e.g /posts/:id/:somethingelse)
    const keys = Array.from(match.route.path.matchAll(/:(\w+)/g)).map(result => result[1]);

    // console.log(Array.from(match.route.path.matchAll(/:(\w+)/g)));
    // console.log(keys);
    // console.log(values);

    return Object.fromEntries(keys.map((key, i) => {
        return [key, values[i]];
    }));
}

// Application Routes 
const router = async () => {    
    const routes = [
        // { path: "/", view: Dashboard },
        // { path: "/posts", view: Posts},
        // { path: "/posts/:id", view: SinglePost},
        // { path: "/edit/:id", view: EditPost},
        // { path: "/create", view: CreatePost},
        // { path: "/settings", view: Settings}
        { path: "/views/register", view: Register}
    ]

    // Test each route for potential match
    const potentialMatches = routes.map(route => {
        return {
            route: route,

            //Returns null if there is no match
            result: location.pathname.match(pathToRegexp(route.path))
        }
    });

    // Get route that matches the url path
    let match = potentialMatches.find(potentialMatch => potentialMatch.result !== null);

    // Handle undefined routes - Default to dashboard
    if(!match) {
        match = {
            result: [location.pathname],
            route: routes[0]
        }
    }

    const view = new match.route.view(getParams(match));
    document.querySelector("#app").innerHTML = await view.getHTML();;
}

// Control navigation using the history API
const navigateTo = url => {
    window.history.pushState(null, null, url);

    router();
}

// Navigate to route when browser 'forward' and 'back' buttons are cliked
window.addEventListener("popstate", router);

// Call the router function  when DOM loads
document.addEventListener("DOMContentLoaded", () => {

    // Navigate to route when nav link is clicked
    document.body.addEventListener("click", (e) => {
        if (e.target.matches("[data-link]")) {
            e.preventDefault();
            navigateTo(e.target.href);
        }
    });
    router();
});