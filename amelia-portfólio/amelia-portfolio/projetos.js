document.addEventListener('DOMContentLoaded', () => {
  const projectsContainer = document.getElementById('projects-container');

  const projects = [
    {
      id: 1,
      name: 'Projecto Educativo',
      description: 'Um projecto para promover a educação nas comunidades locais.',
      image: 'https://picsum.photos/400/300?1'
    },
    {
      id: 2,
      name: 'Projecto Cultural',
      description: 'Projeto que valoriza a cultura e tradições locais através da arte.',
      image: 'https://picsum.photos/400/300?2'
    },
    {
      id: 3,
      name: 'Projecto Social',
      description: 'Focado em ajudar famílias carenciadas com recursos e formação.',
      image: 'https://picsum.photos/400/300?3'
    },
    {
      id: 4,
      name: 'Projecto Ambiental',
      description: 'Iniciativa para preservação ambiental e reciclagem na cidade.',
      image: 'https://picsum.photos/400/300?4'
    },
    {
      id: 5,
      name: 'Projecto de Saúde',
      description: 'Campanhas de saúde e prevenção em diversas comunidades.',
      image: 'https://picsum.photos/400/300?5'
    }
  ];

  projects.forEach(project => {
    const col = document.createElement('div');
    col.className = 'col-md-4';
    col.innerHTML = `
      <div class="card p-3">
        <img src="${project.image}" class="card-img-top" alt="${project.name}">
        <div class="card-body">
          <h5 class="card-title">${project.name}</h5>
          <p class="card-text">${project.description}</p>
          <a href="#" class="btn btn-laranja w-100">Ver mais</a>
        </div>
      </div>
    `;
    projectsContainer.appendChild(col);
  });
});