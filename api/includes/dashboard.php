<?php

require __DIR__ . "/../modules/discord.php";

global $guild_id, $moderator_role_id;

if (!user_is_authorized($guild_id, $moderator_role_id)) {
    session_destroy();

    header('Location: /');
    exit;
}

require __DIR__ . "/../modules/database.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GGF - Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .emoji {
            font-size: 1.5rem;
        }

        .answer-pre {
            white-space: pre-wrap; /* Preserves whitespace and line breaks */
            word-wrap: break-word; /* Breaks long words to avoid overflow */
        }

        .button-group {
            display: flex;
            flex-shrink: 0; /* Ensures buttons don't shrink */
            align-items: center;
            margin-left: 10px; /* Adds some space between the text and buttons */
        }

        .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="d-flex justify-content-between mb-4">
        <h1 class="my-0">Gestion des F.A.Q.</h1>

        <button id="saveButton" class="btn btn-success m-0">Enregistrer</button>
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="managementTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="categories-tab" data-toggle="tab" href="#categories" role="tab" aria-controls="categories" aria-selected="true">Gérer les catégories</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="qna-tab" data-toggle="tab" href="#qna" role="tab" aria-controls="qna" aria-selected="false">Gérer la F.A.Q.</a>
        </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content mt-4">
        <div class="tab-pane fade show active" id="categories" role="tabpanel" aria-labelledby="categories-tab">
            <!-- Categories Management -->
            <div class="card mb-4">
                <div class="card-header">
                    Nouvelle catégorie
                </div>
                <div class="card-body">
                    <form id="addCategoryForm">
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-4">
                                <label for="categoryName">Nom de la catégorie</label>
                                <input type="text" class="form-control" id="categoryName" placeholder="Saisissez le nom" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="categoryEmoji">Emoji</label>
                                <input type="text" class="form-control" id="categoryEmoji" placeholder="Saisissez l'emoji unicode" required>
                            </div>
                            <div class="form-group col-md-4">
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </div>
                    </form>

                    <div class="alert alert-primary" role="alert">
                        La catégorie ne sera pas enregistrée tant qu'elle n'est pas associée à une question.
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Catégories existantes
                </div>
                <div class="card-body">
                    <ul class="list-group" id="categoryList">
                        <!-- Categories will be dynamically added here -->
                    </ul>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="qna" role="tabpanel" aria-labelledby="qna-tab">
            <!-- Add Question Form -->
            <div class="card mb-4">
                <div class="card-header">
                    Nouvelle question
                </div>
                <div class="card-body">
                    <form id="addQuestionForm">
                        <div class="form-group">
                            <label for="category">Categorie</label>
                            <select class="form-control" id="category" required>
                                <!-- Categories will be dynamically populated here -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="question">Question</label>
                            <input type="text" class="form-control" id="question" placeholder="Saisissez la question" required>
                        </div>
                        <div class="form-group">
                            <label for="answer">Réponse</label>
                            <textarea class="form-control" id="answer" rows="8" placeholder="Saisissez la réponse" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </form>
                </div>
            </div>

            <ul class="list-group" id="questionList">
                <!-- Questions will be dynamically added here -->
            </ul>
        </div>
    </div>
</div>

<!-- Modal for updating questions -->
<div class="modal fade" id="updateQuestionModal" tabindex="-1" aria-labelledby="updateQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateQuestionModalLabel">Modifier la question</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="updateQuestionForm">
                    <input type="hidden" id="updateQuestionId">
                    <div class="form-group">
                        <label for="updateCategory">Categorie</label>
                        <select class="form-control" id="updateCategory" required>
                            <!-- Categories will be dynamically populated here -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="updateQuestion">Question</label>
                        <input type="text" class="form-control" id="updateQuestion" required>
                    </div>
                    <div class="form-group">
                        <label for="updateAnswer">Réponse</label>
                        <textarea class="form-control" id="updateAnswer" rows="8" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer les changements</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for updating categories -->
<div class="modal fade" id="updateCategoryModal" tabindex="-1" aria-labelledby="updateCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateCategoryModalLabel">Mettre à jour la catégorie</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="updateCategoryForm">
                    <input type="hidden" id="updateCategoryId">
                    <div class="form-group">
                        <label for="updateCategoryName">Nom de la catégorie</label>
                        <input type="text" class="form-control" id="updateCategoryName" required>
                    </div>
                    <div class="form-group">
                        <label for="updateCategoryEmoji">Emoji</label>
                        <input type="text" class="form-control" id="updateCategoryEmoji" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer les changements</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    let dataHasChanged = false;
    $(window).bind('beforeunload', function() {
        if (dataHasChanged) {
            return "Des modifications n'ont pas été enregistrées. Êtes-vous sûr de vouloir quitter la page?";
        }

        return undefined;
    });

    <?php
    $current_qna = fetch_guild_QNA($guild_id);

    $categories = [];
    $index = 1;
    foreach($current_qna as $qna) {
        $category_name = $qna['category'];
        $category_emoji = $qna['emoji'];

        // Check if the category already exists
        $qna_category = null;
        foreach($categories as $category) {
            if($category['name'] == $category_name) {
                $qna_category = $category;
                break;
            }
        }

        // If the category doesn't exist, add it
        if(!$qna_category) {
            // Generate id from name but keep only alphanumeric characters and replace spaces with underscores
            $category_id = str_replace(' ', '_', $category_name);
            $category_id = preg_replace('/[^a-zA-Z0-9_]/', '', $category_id);
            $category_id = strtolower($category_id);

            $qna_category = [
                'id' => $category_id,
                'name' => $category_name,
                'emoji' => $category_emoji
            ];

            $categories[] = $qna_category;
        }

        $qna['id'] = $index;
        $qna['category'] = $qna_category['id'];
        unset($qna['emoji']);

        $index++;
    }
        ?>

    // Put the categories found in the database into a variable
    let categories = <?php echo json_encode($categories); ?>;
    let questions = <?php echo json_encode($current_qna); ?>;

    // Function to render categories
    function renderCategories() {
        const categorySelect = $('#category');
        const updateCategorySelect = $('#updateCategory');
        const categoryList = $('#categoryList');

        categorySelect.empty();
        updateCategorySelect.empty();
        categoryList.empty();

        categories.forEach(c => {
            categorySelect.append(`<option value="${c.id}">${c.name} ${c.emoji}</option>`);
            updateCategorySelect.append(`<option value="${c.id}">${c.name} ${c.emoji}</option>`);
            categoryList.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="emoji">${c.emoji}</span> ${c.name}
                        </div>
                        <div>
                            <button class="btn btn-info btn-sm mr-2" onclick="editCategory('${c.id}')">Modifier</button>
                            <button class="btn btn-danger btn-sm" onclick="confirmDeleteCategory('${c.id}')">Supprimer</button>
                        </div>
                    </li>
                `);
        });
    }

    // Function to render questions
    function renderQuestions() {
        const questionList = $('#questionList');
        questionList.empty();

        const groupedQuestions = {};

        // Group questions by categories
        questions.forEach(q => {
            if (!groupedQuestions[q.category]) {
                groupedQuestions[q.category] = [];
            }
            groupedQuestions[q.category].push(q);
        });

        // Render each category and its questions
        for (const category in groupedQuestions) {
            const categoryObj = categories.find(c => c.id === category);
            const categoryName = categoryObj ? categoryObj.name : category;
            const categoryEmoji = categoryObj ? categoryObj.emoji : '';

            const questionsHtml = groupedQuestions[category].map(q => {
                return `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start w-100">
                        <div class="flex-grow-1">
                            <b>${q.question}</b>
                            <p class="mb-0"><pre class="mb-0 answer-pre">${q.answer}</pre></p>
                        </div>
                        <div class="button-group">
                            <button class="btn btn-info btn-sm mr-2" onclick="editQuestion(${q.id})">Modifier</button>
                            <button class="btn btn-danger btn-sm" onclick="confirmDeleteQuestion(${q.id})">Supprimer</button>
                        </div>
                    </div>
                </li>
            `;
            }).join('');

            const categoryHtml = `
            <div class="card mb-3">
                <div class="card-header" data-toggle="collapse" data-target="#collapse${category}" aria-expanded="true" aria-controls="collapse${category}">
                    <h5 class="mb-0">
                        <button class="btn btn-link">${categoryEmoji} ${categoryName}</button>
                    </h5>
                </div>

                <div id="collapse${category}" class="collapse show">
                    <div class="card-body">
                        <ul class="list-group">
                            ${questionsHtml}
                        </ul>
                    </div>
                </div>
            </div>
        `;

            questionList.append(categoryHtml);
        }
    }

    // Function to add category
    $('#addCategoryForm').on('submit', function(event) {
        event.preventDefault();
        const newCategory = {
            id: $('#categoryName').val().toLowerCase(),
            name: $('#categoryName').val(),
            emoji: $('#categoryEmoji').val()
        };
        categories.push(newCategory);
        renderCategories();
        this.reset();
    });

    // Function to confirm deletion of a category
    function confirmDeleteCategory(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer la catégorie? Toutes les questions associées seront également supprimées.")) {
            deleteCategory(id);
        }
    }

    // Function to delete category
    function deleteCategory(id) {
        const index = categories.findIndex(c => c.id === id);
        if (index !== -1) {
            categories.splice(index, 1);
            questions = questions.filter(q => q.category !== id);
            renderCategories();
            renderQuestions();

            dataHasChanged = true;
        }
    }

    // Function to edit category
    function editCategory(id) {
        const category = categories.find(c => c.id === id);
        if (category) {
            $('#updateCategoryId').val(category.id);
            $('#updateCategoryName').val(category.name);
            $('#updateCategoryEmoji').val(category.emoji);
            $('#updateCategoryModal').modal('show');
        }
    }

    // Function to update category
    $('#updateCategoryForm').on('submit', function(event) {
        event.preventDefault();
        const id = $('#updateCategoryId').val();
        const updatedCategory = {
            id: id,
            name: $('#updateCategoryName').val(),
            emoji: $('#updateCategoryEmoji').val()
        };
        const index = categories.findIndex(c => c.id === id);
        if (index !== -1) {
            categories[index] = updatedCategory;
            renderCategories();
            renderQuestions();
            $('#updateCategoryModal').modal('hide');

            dataHasChanged = true;
        }
    });

    // Function to add question
    $('#addQuestionForm').on('submit', function(event) {
        event.preventDefault();
        const newQuestion = {
            id: questions.length + 1,
            category: $('#category').val(),
            question: $('#question').val(),
            answer: $('#answer').val()
        };
        questions.push(newQuestion);
        renderQuestions();
        this.reset();

        dataHasChanged = true;
    });

    // Function to confirm deletion of a question
    function confirmDeleteQuestion(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer la question?")) {
            deleteQuestion(id);
        }
    }

    // Function to delete question
    function deleteQuestion(id) {
        const index = questions.findIndex(q => q.id === id);
        if (index !== -1) {
            questions.splice(index, 1);
            renderQuestions();

            dataHasChanged = true;
        }
    }

    // Function to edit question
    function editQuestion(id) {
        const question = questions.find(q => q.id === id);
        if (question) {
            $('#updateQuestionId').val(question.id);
            $('#updateCategory').val(question.category);
            $('#updateQuestion').val(question.question);
            $('#updateAnswer').val(question.answer);
            $('#updateQuestionModal').modal('show');
        }
    }

    // Function to update question
    $('#updateQuestionForm').on('submit', function(event) {
        event.preventDefault();
        const id = parseInt($('#updateQuestionId').val());
        const updatedQuestion = {
            id: id,
            category: $('#updateCategory').val(),
            question: $('#updateQuestion').val(),
            answer: $('#updateAnswer').val()
        };
        const index = questions.findIndex(q => q.id === id);
        if (index !== -1) {
            questions[index] = updatedQuestion;
            renderQuestions();
            $('#updateQuestionModal').modal('hide');

            dataHasChanged = true;
        }
    });

    // Function to save questions as JSON
    $('#saveButton').on('click', function() {
        const questionsWithEmoji = questions.map(q => {
            const category = categories.find(c => c.id === q.category);
            return {
                question: q.question,
                answer: q.answer,
                category: category.name,
                emoji: category ? category.emoji : ''
            };
        });

        $.ajax({
            url: '/dashboard/save',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(questionsWithEmoji),
            success: function() {
                dataHasChanged = false;
                alert('La F.A.Q. a été enregistrée avec succès.');
            },
            error: function() {
                alert('Une erreur s\'est produite lors de l\'enregistrement de la F.A.Q.');
            }
        });
    });

    // Initial render
    renderCategories();
    renderQuestions();
</script>
</body>
</html>
