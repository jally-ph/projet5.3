<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Books;
use App\Entity\Chapter;
use App\Entity\Comment;
use App\Form\SearchType;
use App\Form\CommentType;
use App\Repository\BooksRepository;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    /**
     * @Route("/app", name="app")
     */
    public function index(Request $request, PaginatorInterface $paginator, BooksRepository $repo)
    {
        //barre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        $donnees = $repo->findAll();
        // dd($book);
        $books = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            5
        );

        $books->setTemplate('app/twitter_bootstrap_v4_pagination.html.twig');
        
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
            'books' => $books,
            'formSearch' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        //barre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        return $this->render('app/home.html.twig', [
            'subtitle' => "Bienvenu sur mon site",
            'formSearch' => $searchForm->createView()
        ]);

    }

    // /**
    //  * @Route("/search/{search}", name="search")
    //  */
    // public function search($search, Request $request)
    // {
    //     $searchForm = $this->createForm(SearchType::class, null);

    //     if($request->isMethod("POST")){
    //         $searchForm->handleRequest($request);

    //         if($searchForm->isSubmitted() && $searchForm->isValid()){
    //             return $this->redirectToRoute("search", [
    //                 'search' => $searchForm["search"]->getData()
    //             ]);
    //         }
    //     }
        







    // }

    
    /**
     * @Route("/categories", name="categories")
     */
    public function categoryPage(Request $request)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        return $this->render('app/categories.html.twig', [
            'formSearch' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/category/{category}", name="show_category")
     */
    public function getCategory($category, Request $request)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        $bookRepository = $this->getDoctrine()->getRepository(Books::class);
        $books = $bookRepository->findByCategory($category);

        return $this->render('app/category.html.twig', [
            'books' => $books,
            'category' => $category,
            'formSearch' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/app/newbook", name="new_book")
     */
    public function newBook(Books $book = null, Request $request, EntityManagerInterface $manager, UserInterface $user)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        if(!$book){
            $book = new Books();
        }
        
        $form = $this->createFormBuilder($book)
                    ->add('title')
                    ->add('content')
                    ->add('category', ChoiceType::class, [
                        'choices'  => [
                            'littérature' => 'littérature',
                            'fantastique' => 'fantastique',
                            'romance' => 'romance',
                            'poésie' => 'poésie',
                            'policier' => 'policier',
                            'essai' => 'essai'
                        ],
                    ])
                    ->add('public')
                    ->add('completed')
                    ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // dd($book->getId());
            $book->setDate(new \DateTime());

            $username = $user->getUsername();
            $book->setAuthor($username);
            
            $manager->persist($book);
            $manager->flush();

            return $this->redirectToRoute('app_show', ['id' => $book->getId()]);
        }

        return $this->render('app/newBook.html.twig', [
            'formNewBook' => $form->createView(),
            'titlePage' => $book->getId() !== null,
            'editMode' => $book->getId() !== null,
            'formSearch' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/app/newchapter/{id}", name="new_chapter")
     */
    public function newChapter($id, Chapter $chapter = null, Request $request, EntityManagerInterface $manager)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        if(!$chapter){
            $chapter = new Chapter();
        }

        $form = $this->createFormBuilder($chapter)
                    ->add('title')
                    ->add('content')
                    ->add('public')
                    ->add('completed')
                   
                    ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $chapter->setPublishedDate(new \DateTime());
            //récup le book.id
            $booksRepository = $this->getDoctrine()->getRepository(Books::class);
            $book = $booksRepository->find($id);
            $chapter->setBooks($book);

            
            $manager->persist($chapter);
            $manager->flush();

            return $this->redirectToRoute('app_show', ['id' => $book->getId()]);
        }
        return $this->render('app/newChapter.html.twig', [
            'formNewChapter' => $form->createView(),
            'titlePage' => $chapter->getId() !== null,
            'editMode' => $chapter->getId() !== null,
            'formSearch' => $searchForm->createView()
        ]);
    }


    /**
     * @Route("/app/{id}", name="app_show")
     */
    public function showBook($id, Books $book, Request $request)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        $booksRepository = $this->getDoctrine()->getRepository(Books::class);
        $book = $booksRepository->find($id);
        $chapterRepository = $this->getDoctrine()->getRepository(Chapter::class);
        $chapters = $chapterRepository->findAllByBook($book->getId());

        return $this->render('app/showBook.html.twig', [
            'book' => $book,
            'chapters' => $chapters,
            'formSearch' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/chapter/{id}", name="chapter_show")
     */
    public function showChapter($id, Chapter $chapter, Comment $comment = null, Request $request, EntityManagerInterface $manager, UserInterface $user = null)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $username = $user->getUsername();
            $comment->setAuthor($username);

            $comment->setCreatedAt(new \DateTime());
            $comment->setChapter($chapter);

            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('chapter_show', ['id' => $chapter->getId()]);
        }

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $commentRepository->findAllByChapter($chapter->getId());

        return $this->render('app/showChapter.html.twig', [
            'chapter' => $chapter,
            'commentForm' => $form->createView(),
            'comments' => $comments,
            'formSearch' => $searchForm->createView()
        ]);
    }


    /**
      * @Route("/editcomment/{id}", name="edit_comment")
      */
     public function editComment($id, Comment $comment, Request $request, EntityManagerInterface $manager)
     {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }
        
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //récup le user
            // $username = $user->getUsername();
            // $comment->setAuthor($username);
            
            $comment->setCreatedAt(new \DateTime());

            $manager->persist($comment);
            $manager->flush();

             // on récup l'id du chap
            $chapter = $comment->getChapter()->getId();

            return $this->redirectToRoute('chapter_show', ['id' => $chapter]);
        }

        return $this->render('app/formEditComment.html.twig', [
             'formEditComment' => $form->createView(),
             'formSearch' => $searchForm->createView()
         ]);
    }

    /**
     * @Route("/editchapter/{id}", name="edit_chapter")
     */
    public function editChapter($id, Chapter $chapter, Request $request, EntityManagerInterface $manager)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }
     
        $form = $this->createFormBuilder($chapter)
                    ->add('title')
                    ->add('content')
                    ->add('public')
                    ->add('completed')
                    ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $chapter->setPublishedDate(new \DateTime());
           
            $bookId = $chapter->getBooks()->getId();
            
            $manager->persist($chapter);
            $manager->flush();

            return $this->redirectToRoute('app_show', ['id' => $bookId]);
        }
        return $this->render('app/newChapter.html.twig', [
            'formNewChapter' => $form->createView(),
            'titlePage' => $chapter->getId() !== null,
            'editMode' => $chapter->getId() !== null,
            'formSearch' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/editbook/{id}", name="edit_book")
     */
    public function editBook($id, Books $book, Request $request, EntityManagerInterface $manager, UserInterface $user)
    {
        // barrre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        $form = $this->createFormBuilder($book)
                    ->add('title')
                    ->add('content')
                    ->add('public')
                    ->add('completed')
                    ->getForm();

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ){
            
             $book->setDate(new \DateTime());

            // $username = $user->getUsername();
            // $book->setAuthor($username);

            $bookId = $book->getId();
            

            $manager->persist($book);
            $manager->flush();

            
            return $this->redirectToRoute('app_show', ['id' => $bookId]);
        }

        return $this->render('app/newBook.html.twig', [
            'formNewBook' => $form->createView(),
            'titlePage' => $book->getId() !== null,
            'editMode' => $book->getId() !== null,
            'formSearch' => $searchForm->createView()
        ]);
    }


    /**
     * @Route("/delete/comment/{id}", name="delete_comment")
     */
    public function deleteComment(Comment $comment, EntityManagerInterface $manager)
    {
        
        $manager->remove($comment);
        $manager->flush();

        $chapter = $comment->getChapter()->getId();
        return $this->redirectToRoute('chapter_show', ['id' => $chapter]);
        
    }

    /**
     * @Route("/delete/chapter/{id}", name="delete_chapter")
     */
    public function deleteChapter(Chapter $chapter, EntityManagerInterface $manager)
    {

        $manager->remove($chapter);
        $manager->flush();

        $bookId = $chapter->getBooks()->getId();
        return $this->redirectToRoute('app_show', ['id' => $bookId]);
        
    }

    /**
     * @Route("/delete/book/{id}", name="delete_book")
     */
    public function deleteBook($id, Books $book, EntityManagerInterface $manager)
    {
        $chapters = $this->getDoctrine()->getRepository(Chapter::class)->findAllByBook($id);

        foreach ($chapters as $chapter){
            $manager->remove($chapter);
        }
        
        $manager->flush();
        $manager->remove($book);
        $manager->flush();

        return $this->redirectToRoute('app');

    }

    


}
