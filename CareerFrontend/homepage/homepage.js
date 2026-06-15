const careers = [

{
name:"Web Development",
img:"https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=900&q=80"
},

{
name:"Data Science",
img:"https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=900&q=80"
},

{
name:"UI/UX Design",
img:"https://images.unsplash.com/photo-1559028012-481c04fa702d?auto=format&fit=crop&w=900&q=80"
},

{
name:"Cybersecurity",
img:"https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=900&q=80"
},

{
name:"Mobile App Development",
img:"https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=900&q=80"
},

{
name:" Machine Learning",
img:"https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=900&q=80"
}

];
document.getElementById("careerCards").innerHTML =
careers.map(x=>`

<div class="career"
style="background-image:
linear-gradient(rgba(0,0,0,0.45),rgba(0,0,0,0.45)),
url('${x.img}')">

<h2>${x.name}</h2>

<p style="color:white">
Master your professional skills.
</p>

</div>

`).join("");