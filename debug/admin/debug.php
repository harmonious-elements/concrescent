<?php

require_once dirname(__FILE__).'/../lib/database/attendee.php';
require_once dirname(__FILE__).'/../lib/util/slack.php';
require_once dirname(__FILE__).'/admin.php';

cm_admin_check_permission('debug', '*');

$atdb = new cm_attendee_db($db);

$badge_type_ids = array_keys($atdb->get_badge_type_name_map());

$notes = array(
	'C', 'D', 'E', 'F', 'G', 'A', 'B',
	'C#', 'D#', 'F#', 'G#', 'A#',
	'Db', 'Eb', 'Gb', 'Ab', 'Bb'
);

$first_names = array(
	'Aaron', 'Abby', 'Abdul', 'Abel', 'Abigail', 'Abraham', 'Abram', 'Ada',
	'Adam', 'Adan', 'Adolfo', 'Adrain', 'Adria', 'Adrian', 'Adriana',
	'Adriane', 'Adrianne', 'Adrienne', 'Agustin', 'Ahmad', 'Aida', 'Aileen',
	'Aimee', 'Aisha', 'Aja', 'Al', 'Alan', 'Alana', 'Alanna', 'Albert',
	'Alberto', 'Aldo', 'Alec', 'Alecia', 'Alejandra', 'Alejandro', 'Alex',
	'Alexa', 'Alexander', 'Alexandra', 'Alexandria', 'Alexis', 'Alfonso',
	'Alfonzo', 'Alfred', 'Alfredo', 'Ali', 'Alice', 'Alicia', 'Alisa',
	'Alisha', 'Alison', 'Alissa', 'Allan', 'Allen', 'Allison', 'Allyson',
	'Alma', 'Alonzo', 'Alphonso', 'Alton', 'Alvaro', 'Alvin', 'Alysia',
	'Alyson', 'Alyssa', 'Amanda', 'Amber', 'Amelia', 'Ami', 'Amie', 'Amos',
	'Amy', 'Ana', 'Anastasia', 'Anderson', 'Andra', 'Andre', 'Andrea',
	'Andreas', 'Andres', 'Andrew', 'Andria', 'Andy', 'Angel', 'Angela',
	'Angelia', 'Angelica', 'Angelina', 'Angeline', 'Angelique', 'Angelita',
	'Angelo', 'Angie', 'Anika', 'Anissa', 'Anita', 'Anitra', 'Anjanette',
	'Ann', 'Anna', 'Anne', 'Annemarie', 'Annette', 'Annie', 'Annmarie',
	'Anthony', 'Antione', 'Antionette', 'Antoine', 'Antoinette', 'Anton',
	'Antonia', 'Antonio', 'Antony', 'Antwan', 'Antwon', 'April', 'Araceli',
	'Archie', 'Aretha', 'Ari', 'Aric', 'Ariel', 'Arlene', 'Armando', 'Arnold',
	'Arnulfo', 'Aron', 'Arron', 'Arthur', 'Arturo', 'Ashley', 'Athena',
	'Aubrey', 'Audra', 'Audrey', 'August', 'Augustine', 'Aurelio', 'Aurora',
	'Austin', 'Autumn', 'Avery', 'Ayanna', 'Barbara', 'Barney', 'Baron',
	'Barrett', 'Barry', 'Bart', 'Barton', 'Beatrice', 'Beatriz', 'Beau',
	'Becky', 'Belinda', 'Ben', 'Benita', 'Benito', 'Benjamin', 'Bennett',
	'Bennie', 'Benny', 'Bernadette', 'Bernard', 'Bernardo', 'Bernice', 'Bert',
	'Bertha', 'Beth', 'Bethany', 'Betsy', 'Betty', 'Beverly', 'Bianca',
	'Bill', 'Billie', 'Billy', 'Blaine', 'Blair', 'Blake', 'Blanca', 'Bob',
	'Bobbi', 'Bobbie', 'Bobby', 'Bonita', 'Bonnie', 'Boyd', 'Brad', 'Braden',
	'Bradford', 'Bradley', 'Bradly', 'Brady', 'Brain', 'Brandee', 'Branden',
	'Brandi', 'Brandie', 'Brandon', 'Brandy', 'Brannon', 'Brant', 'Bree',
	'Brenda', 'Brendan', 'Brendon', 'Brenna', 'Brennan', 'Brent', 'Brenton',
	'Bret', 'Brett', 'Brian', 'Briana', 'Brianna', 'Brianne', 'Brice',
	'Bridget', 'Bridgett', 'Bridgette', 'Brien', 'Brigette', 'Britt',
	'Brittany', 'Brock', 'Broderick', 'Bronson', 'Brook', 'Brooke', 'Brooks',
	'Bruce', 'Bryan', 'Bryant', 'Bryce', 'Bryon', 'Buck', 'Buddy', 'Buffy',
	'Burton', 'Byron', 'Cale', 'Caleb', 'Callie', 'Calvin', 'Cameron',
	'Camille', 'Candace', 'Candi', 'Candice', 'Candida', 'Candy', 'Cara',
	'Caren', 'Carey', 'Cari', 'Carie', 'Carin', 'Carisa', 'Carissa', 'Carl',
	'Carla', 'Carlo', 'Carlos', 'Carlton', 'Carly', 'Carmela', 'Carmelo',
	'Carmen', 'Carol', 'Carole', 'Carolina', 'Caroline', 'Carolyn', 'Carri',
	'Carrie', 'Carroll', 'Carson', 'Carter', 'Cary', 'Caryn', 'Casandra',
	'Casey', 'Cassandra', 'Cassie', 'Catherine', 'Cathleen', 'Cathy',
	'Catina', 'Catrina', 'Cecelia', 'Cecil', 'Cecilia', 'Cedric', 'Cedrick',
	'Celeste', 'Celia', 'Celina', 'Cesar', 'Chad', 'Chadd', 'Chadrick',
	'Chadwick', 'Chaim', 'Chance', 'Chanda', 'Chandra', 'Chanel', 'Chantel',
	'Charisse', 'Charity', 'Charla', 'Charlene', 'Charles', 'Charley',
	'Charlie', 'Charlotte', 'Charmaine', 'Chase', 'Chasity', 'Chastity',
	'Chauncey', 'Che', 'Chelsea', 'Cheri', 'Cherie', 'Cheryl', 'Chester',
	'Chet', 'Chiquita', 'Chris', 'Chrissy', 'Christa', 'Christal', 'Christel',
	'Christen', 'Christi', 'Christian', 'Christie', 'Christin', 'Christina',
	'Christine', 'Christoper', 'Christopher', 'Christy', 'Chrystal',
	'Chuck', 'Cindy', 'Claire', 'Clara', 'Clarence', 'Clarissa', 'Clark',
	'Claude', 'Claudia', 'Claudine', 'Clay', 'Clayton', 'Cleveland',
	'Cliff', 'Clifford', 'Clifton', 'Clint', 'Clinton', 'Clyde', 'Coby',
	'Cody', 'Colby', 'Cole', 'Coleen', 'Colette', 'Colin', 'Colleen',
	'Collin', 'Connie', 'Conrad', 'Constance', 'Constantine', 'Consuelo',
	'Cora', 'Cordell', 'Corey', 'Cori', 'Corina', 'Corinna', 'Corinne',
	'Cornelius', 'Cornell', 'Corrie', 'Corrine', 'Cortez', 'Cortney',
	'Cory', 'Courtney', 'Coy', 'Craig', 'Cristi', 'Cristina', 'Cristopher',
	'Cristy', 'Cruz', 'Crystal', 'Curt', 'Curtis', 'Cynthia', 'Cyrus',
	'Daisy', 'Dale', 'Dallas', 'Damaris', 'Dameon', 'Damian', 'Damien',
	'Damion', 'Damon', 'Damond', 'Dan', 'Dana', 'Dane', 'Danelle',
	'Danette', 'Danial', 'Daniel', 'Daniela', 'Danielle', 'Danita',
	'Danna', 'Dannie', 'Danny', 'Dante', 'Daphne', 'Dara', 'Darby',
	'Darci', 'Darcie', 'Darcy', 'Daren', 'Darian', 'Darin', 'Darius',
	'Darla', 'Darlene', 'Darnell', 'Daron', 'Darrel', 'Darrell', 'Darren',
	'Darrick', 'Darrin', 'Darron', 'Darryl', 'Darwin', 'Daryl', 'Dave',
	'David', 'Davin', 'Davina', 'Davis', 'Dawn', 'Dax', 'Dayna', 'Dean',
	'Deana', 'Deandre', 'Deangelo', 'Deann', 'Deanna', 'Deanne', 'Debbie',
	'Debora', 'Deborah', 'Debra', 'Dedric', 'Dedrick', 'Dee', 'Deena',
	'Deidra', 'Deidre', 'Deirdre', 'Dejuan', 'Delbert', 'Delia', 'Delilah',
	'Della', 'Delores', 'Delvin', 'Demarcus', 'Demetria', 'Demetrius',
	'Demond', 'Dena', 'Denice', 'Denis', 'Denise', 'Dennis', 'Denny',
	'Denver', 'Deon', 'Dereck', 'Derek', 'Deric', 'Derick', 'Derik',
	'Deron', 'Derrick', 'Deshawn', 'Desiree', 'Desmond', 'Destiny',
	'Devin', 'Devon', 'Dewayne', 'Dewey', 'Dexter', 'Diana', 'Diane',
	'Dianna', 'Dianne', 'Diego', 'Dina', 'Dino', 'Dion', 'Dionne', 'Dirk',
	'Dixie', 'Dolores', 'Domingo', 'Dominic', 'Dominick', 'Dominique',
	'Don', 'Donald', 'Donavan', 'Donell', 'Donna', 'Donnell', 'Donnie',
	'Donny', 'Donovan', 'Donta', 'Donte', 'Dora', 'Doreen', 'Dorian',
	'Doris', 'Dorothy', 'Doug', 'Douglas', 'Douglass', 'Doyle', 'Drew',
	'Duane', 'Duncan', 'Dustin', 'Dusty', 'Dwain', 'Dwayne', 'Dwight',
	'Dylan', 'Earl', 'Earnest', 'Ebony', 'Eddie', 'Eddy', 'Edgar',
	'Edgardo', 'Edith', 'Edmond', 'Edmund', 'Edna', 'Eduardo', 'Edward',
	'Edwardo', 'Edwin', 'Efrain', 'Efren', 'Eileen', 'Elaine', 'Elbert',
	'Eldon', 'Eleanor', 'Elena', 'Eli', 'Elias', 'Elijah', 'Elisa',
	'Elisabeth', 'Elise', 'Eliseo', 'Elisha', 'Elissa', 'Eliza', 'Elizabeth',
	'Ella', 'Ellen', 'Elliot', 'Elliott', 'Ellis', 'Elmer', 'Eloy', 'Elsa',
	'Elton', 'Elvin', 'Elvira', 'Elvis', 'Emanuel', 'Emery', 'Emil', 'Emilie',
	'Emilio', 'Emily', 'Emma', 'Emmanuel', 'Emmett', 'Enrique', 'Eric',
	'Erica', 'Erich', 'Erick', 'Ericka', 'Erik', 'Erika', 'Erin', 'Ernest',
	'Ernesto', 'Ernie', 'Errol', 'Ervin', 'Erwin', 'Esmeralda', 'Esperanza',
	'Esteban', 'Esther', 'Ethan', 'Ethel', 'Eugene', 'Eugenia', 'Eunice',
	'Eva', 'Evan', 'Eve', 'Evelyn', 'Everett', 'Ezekiel', 'Ezra', 'Fabian',
	'Faith', 'Farrah', 'Fatima', 'Fawn', 'Faye', 'Federico', 'Felecia',
	'Felicia', 'Felipe', 'Felisha', 'Felix', 'Fernando', 'Fidel', 'Florence',
	'Floyd', 'Forest', 'Forrest', 'Frances', 'Francesca', 'Francesco',
	'Francine', 'Francis', 'Francisco', 'Franco', 'Frank', 'Frankie',
	'Franklin', 'Fred', 'Freda', 'Freddie', 'Freddy', 'Frederic',
	'Frederick', 'Fredrick', 'Gabriel', 'Gabriela', 'Gabrielle', 'Gail',
	'Galen', 'Garland', 'Garret', 'Garrett', 'Garrick', 'Garry', 'Garth',
	'Gary', 'Gavin', 'Gayle', 'Gena', 'Genaro', 'Gene', 'Geneva',
	'Genevieve', 'Geoffrey', 'George', 'Georgette', 'Georgia', 'Georgina',
	'Gerald', 'Geraldine', 'Gerard', 'Gerardo', 'Germaine', 'German',
	'Gerry', 'Gilbert', 'Gilberto', 'Gina', 'Ginger', 'Ginny', 'Gino',
	'Giovanni', 'Giuseppe', 'Gladys', 'Glen', 'Glenda', 'Glenn', 'Gloria',
	'Gonzalo', 'Gordon', 'Grace', 'Graciela', 'Grady', 'Graham', 'Grant',
	'Greg', 'Gregg', 'Greggory', 'Gregorio', 'Gregory', 'Greta', 'Gretchen',
	'Griselda', 'Grover', 'Guadalupe', 'Guillermo', 'Gus', 'Gustavo', 'Guy',
	'Gwen', 'Gwendolyn', 'Hal', 'Haley', 'Hank', 'Hannah', 'Hans', 'Harlan',
	'Harley', 'Harmony', 'Harold', 'Harrison', 'Harry', 'Harvey', 'Hassan',
	'Hazel', 'Heath', 'Heather', 'Hector', 'Heidi', 'Helen', 'Helena',
	'Henry', 'Herbert', 'Heriberto', 'Herman', 'Hilary', 'Hilda', 'Hillary',
	'Hiram', 'Holli', 'Hollie', 'Holly', 'Homer', 'Hope', 'Horace', 'Howard',
	'Hubert', 'Hugh', 'Hugo', 'Humberto', 'Hunter', 'Ian', 'Ida', 'Ignacio',
	'Imelda', 'India', 'Ingrid', 'Ira', 'Irene', 'Iris', 'Irma', 'Irvin',
	'Irving', 'Isaac', 'Isabel', 'Isaiah', 'Isidro', 'Ismael', 'Israel',
	'Issac', 'Ivan', 'Ivette', 'Ivy', 'Jabari', 'Jack', 'Jackie', 'Jackson',
	'Jaclyn', 'Jacob', 'Jacqueline', 'Jacquelyn', 'Jacques', 'Jada', 'Jade',
	'Jaime', 'Jaimie', 'Jake', 'Jamaal', 'Jamal', 'Jamar', 'Jameel', 'Jamel',
	'James', 'Jameson', 'Jamey', 'Jami', 'Jamie', 'Jamil', 'Jamila', 'Jamin',
	'Jamison', 'Jammie', 'Jan', 'Jana', 'Jane', 'Janeen', 'Janel', 'Janell',
	'Janelle', 'Janet', 'Janette', 'Janice', 'Janie', 'Janine', 'Janis',
	'Janna', 'Jared', 'Jarod', 'Jarred', 'Jarrett', 'Jarrod', 'Jarvis',
	'Jasen', 'Jasmin', 'Jasmine', 'Jason', 'Jasper', 'Javier', 'Jay',
	'Jayme', 'Jayson', 'Jean', 'Jeana', 'Jeanette', 'Jeanie', 'Jeanine',
	'Jeanna', 'Jeanne', 'Jeannette', 'Jeannie', 'Jeannine', 'Jed', 'Jedediah',
	'Jeff', 'Jefferey', 'Jefferson', 'Jeffery', 'Jeffrey', 'Jeffry', 'Jena',
	'Jenifer', 'Jenna', 'Jenni', 'Jennie', 'Jennifer', 'Jenny', 'Jerad',
	'Jerald', 'Jeramie', 'Jeramy', 'Jered', 'Jereme', 'Jeremey', 'Jeremiah',
	'Jeremie', 'Jeremy', 'Jeri', 'Jermaine', 'Jermey', 'Jerod', 'Jerome',
	'Jeromy', 'Jerri', 'Jerrod', 'Jerrold', 'Jerry', 'Jess', 'Jesse',
	'Jessica', 'Jessie', 'Jesus', 'Jevon', 'Jill', 'Jillian', 'Jim',
	'Jimmie', 'Jimmy', 'Jo', 'Joan', 'Joann', 'Joanna', 'Joanne', 'Joaquin',
	'Jocelyn', 'Jodi', 'Jodie', 'Jody', 'Joe', 'Joel', 'Joelle', 'Joesph',
	'Joey', 'Johanna', 'John', 'Johnathan', 'Johnathon', 'Johnna', 'Johnnie',
	'Johnny', 'Jolene', 'Jolie', 'Jon', 'Jonah', 'Jonas', 'Jonathan',
	'Jonathon', 'Joni', 'Jordan', 'Jorge', 'Jose', 'Josef', 'Josefina',
	'Joseph', 'Josephine', 'Josette', 'Josh', 'Joshua', 'Josiah', 'Josie',
	'Josue', 'Jovan', 'Joy', 'Joyce', 'Juan', 'Juana', 'Juanita', 'Judd',
	'Jude', 'Judith', 'Judson', 'Judy', 'Juli', 'Julia', 'Julian', 'Juliana',
	'Julianne', 'Julie', 'Juliet', 'Julio', 'Julius', 'June', 'Junior',
	'Justin', 'Justina', 'Justine', 'Kami', 'Kandi', 'Kara', 'Kareem',
	'Karen', 'Kari', 'Karie', 'Karin', 'Karina', 'Karl', 'Karla', 'Karri',
	'Karrie', 'Karyn', 'Kasey', 'Kate', 'Katharine', 'Katherine', 'Kathleen',
	'Kathrine', 'Kathryn', 'Kathy', 'Katie', 'Katina', 'Katrina', 'Katy',
	'Kay', 'Kayla', 'Keely', 'Keenan', 'Keisha', 'Keith', 'Kelley', 'Kelli',
	'Kellie', 'Kelly', 'Kelsey', 'Kelvin', 'Ken', 'Kendall', 'Kendra',
	'Kendrick', 'Kenisha', 'Kenneth', 'Kennith', 'Kenny', 'Kent', 'Kenton',
	'Kenya', 'Kenyatta', 'Kenyon', 'Keri', 'Kermit', 'Kerri', 'Kerrie',
	'Kerry', 'Kesha', 'Keven', 'Kevin', 'Kia', 'Kim', 'Kimberlee',
	'Kimberley', 'Kimberly', 'Kip', 'Kira', 'Kirby', 'Kirk', 'Kirsten',
	'Kirstin', 'Kirt', 'Kisha', 'Kizzy', 'Korey', 'Kori', 'Kory', 'Kraig',
	'Kris', 'Krista', 'Kristal', 'Kristen', 'Kristi', 'Kristian', 'Kristie',
	'Kristin', 'Kristina', 'Kristine', 'Kristofer', 'Kristoffer',
	'Kristopher', 'Kristy', 'Krystal', 'Kurt', 'Kurtis', 'Kyla', 'Kyle',
	'Kylie', 'Lacey', 'Lacy', 'Ladonna', 'Lakeisha', 'Lakesha', 'Lakeshia',
	'Lakisha', 'Lamar', 'Lamont', 'Lana', 'Lance', 'Landon', 'Lane', 'Lanny',
	'Laquita', 'Lara', 'Larissa', 'Laron', 'Larry', 'Lars', 'Lashanda',
	'Lashawn', 'Lashonda', 'Latanya', 'Latarsha', 'Latasha', 'Latisha',
	'Latonia', 'Latonya', 'Latosha', 'Latoya', 'Latrice', 'Latricia',
	'Laura', 'Laurel', 'Lauren', 'Laurence', 'Lauri', 'Laurie', 'Lavar',
	'Lawanda', 'Lawrence', 'Layla', 'Lea', 'Leah', 'Leann', 'Leanna',
	'Leanne', 'Lee', 'Leeann', 'Leif', 'Leigh', 'Leila', 'Leilani',
	'Leland', 'Lena', 'Lenny', 'Lenora', 'Leo', 'Leon', 'Leona', 'Leonard',
	'Leonardo', 'Leonel', 'Leopoldo', 'Leroy', 'Lesa', 'Lesley', 'Leslie',
	'Lester', 'Leticia', 'Letitia', 'Levar', 'Levi', 'Lewis', 'Liam',
	'Lilia', 'Liliana', 'Lillian', 'Lillie', 'Lincoln', 'Linda', 'Lindsay',
	'Lindsey', 'Lindy', 'Linwood', 'Lionel', 'Lisa', 'Lisette', 'Lissette',
	'Liza', 'Lloyd', 'Logan', 'Lois', 'Lola', 'Lon', 'Lonnie', 'Lonny',
	'Lora', 'Loren', 'Lorena', 'Lorenzo', 'Loretta', 'Lori', 'Lorie',
	'Lorna', 'Lorraine', 'Lorrie', 'Louie', 'Louis', 'Louise', 'Lourdes',
	'Lowell', 'Lucas', 'Lucia', 'Lucille', 'Lucinda', 'Lucretia', 'Lucy',
	'Luis', 'Luke', 'Luther', 'Luz', 'Lydia', 'Lyle', 'Lynda', 'Lynette',
	'Lynn', 'Lynne', 'Lynnette', 'Mack', 'Madeline', 'Magdalena', 'Maggie',
	'Malcolm', 'Malik', 'Malinda', 'Malissa', 'Mandi', 'Mandy', 'Manuel',
	'Mara', 'Maranda', 'Marc', 'Marcel', 'Marcella', 'Marcellus', 'Marci',
	'Marcia', 'Marcie', 'Marco', 'Marcos', 'Marcus', 'Marcy', 'Margaret',
	'Margarita', 'Margie', 'Margo', 'Mari', 'Maria', 'Mariah', 'Marian',
	'Marianne', 'Mariano', 'Maribel', 'Maricela', 'Marie', 'Marilyn',
	'Marina', 'Mario', 'Marion', 'Marisa', 'Marisela', 'Marisol', 'Marissa',
	'Maritza', 'Marjorie', 'Mark', 'Markus', 'Marla', 'Marlena', 'Marlene',
	'Marlin', 'Marlo', 'Marlon', 'Marni', 'Marnie', 'Marques', 'Marquis',
	'Marsha', 'Marshall', 'Marta', 'Martha', 'Martin', 'Martina', 'Marty',
	'Marvin', 'Mary', 'Maryann', 'Mason', 'Mathew', 'Matt', 'Matthew',
	'Maura', 'Maureen', 'Maurice', 'Mauricio', 'Max', 'Maxine', 'Maxwell',
	'Maya', 'Mayra', 'Meagan', 'Mechelle', 'Megan', 'Meghan', 'Melanie',
	'Melinda', 'Melisa', 'Melissa', 'Mellisa', 'Mellissa', 'Melodie',
	'Melody', 'Melonie', 'Melvin', 'Mendy', 'Mercedes', 'Meredith', 'Merle',
	'Mia', 'Micah', 'Michael', 'Michaela', 'Michale', 'Micheal', 'Michel',
	'Michele', 'Michell', 'Michelle', 'Mickey', 'Miguel', 'Mike', 'Mikel',
	'Mildred', 'Miles', 'Milton', 'Mindi', 'Mindy', 'Miranda', 'Miriam',
	'Misti', 'Misty', 'Mitchel', 'Mitchell', 'Mitzi', 'Moises', 'Mollie',
	'Molly', 'Mona', 'Monica', 'Monika', 'Monique', 'Monte', 'Monty',
	'Morgan', 'Morris', 'Moses', 'Moshe', 'Myles', 'Myra', 'Myrna', 'Myron',
	'Nadia', 'Nadine', 'Nakia', 'Nakisha', 'Nancy', 'Nanette', 'Naomi',
	'Natalia', 'Natalie', 'Natasha', 'Nathan', 'Nathanael', 'Nathanial',
	'Nathaniel', 'Neal', 'Ned', 'Neil', 'Nellie', 'Nelson', 'Nestor',
	'Nichol', 'Nicholas', 'Nichole', 'Nick', 'Nickolas', 'Nicky', 'Nicolas',
	'Nicole', 'Nicolle', 'Nigel', 'Niki', 'Nikia', 'Nikita', 'Nikki',
	'Nikole', 'Nina', 'Noah', 'Noe', 'Noel', 'Noelle', 'Noemi', 'Nolan',
	'Nora', 'Norberto', 'Norma', 'Norman', 'Norris', 'Octavia', 'Octavio',
	'Olga', 'Oliver', 'Olivia', 'Omar', 'Orlando', 'Oscar', 'Osvaldo',
	'Otis', 'Owen', 'Pablo', 'Paige', 'Pam', 'Pamela', 'Paris', 'Pat',
	'Patrice', 'Patricia', 'Patrick', 'Patsy', 'Patti', 'Patty', 'Paul',
	'Paula', 'Paulette', 'Pauline', 'Paulo', 'Pearl', 'Pedro', 'Peggy',
	'Penelope', 'Penny', 'Percy', 'Perry', 'Pete', 'Peter', 'Phil', 'Philip',
	'Phillip', 'Phyllis', 'Pierre', 'Polly', 'Preston', 'Priscilla', 'Qiana',
	'Quentin', 'Quincy', 'Quinn', 'Quintin', 'Quinton', 'Rachael', 'Racheal',
	'Rachel', 'Rachelle', 'Rae', 'Rafael', 'Raheem', 'Rahsaan', 'Raina',
	'Ralph', 'Ramiro', 'Ramon', 'Ramona', 'Randal', 'Randall', 'Randell',
	'Randi', 'Randolph', 'Randy', 'Raphael', 'Raquel', 'Rashad', 'Rasheed',
	'Rashida', 'Raul', 'Raven', 'Ray', 'Raymond', 'Raymundo', 'Rebeca',
	'Rebecca', 'Rebekah', 'Reed', 'Regan', 'Reggie', 'Regina', 'Reginald',
	'Reid', 'Rena', 'Renae', 'Rene', 'Renee', 'Renita', 'Reuben', 'Rex',
	'Reynaldo', 'Rhett', 'Rhiannon', 'Rhonda', 'Ricardo', 'Richard',
	'Richelle', 'Richie', 'Rick', 'Rickey', 'Rickie', 'Ricky', 'Rico',
	'Rigoberto', 'Riley', 'Rita', 'Rob', 'Robb', 'Robbie', 'Robby',
	'Robert', 'Roberta', 'Roberto', 'Robin', 'Robyn', 'Rocco', 'Rochelle',
	'Rocio', 'Rocky', 'Rod', 'Roderick', 'Rodger', 'Rodney', 'Rodolfo',
	'Rodrick', 'Rodrigo', 'Rogelio', 'Roger', 'Roland', 'Rolando', 'Roman',
	'Ron', 'Ronald', 'Ronda', 'Ronnie', 'Ronny', 'Roosevelt', 'Rory', 'Rosa',
	'Rosalie', 'Rosalind', 'Rosalinda', 'Rosalyn', 'Rosanna', 'Rosario',
	'Roscoe', 'Rose', 'Rosemarie', 'Rosemary', 'Rosie', 'Ross', 'Roxanna',
	'Roxanne', 'Roy', 'Royce', 'Ruben', 'Ruby', 'Rudolph', 'Rudy', 'Rufus',
	'Russel', 'Russell', 'Rusty', 'Ruth', 'Ryan', 'Sabrina', 'Sadie',
	'Salina', 'Sally', 'Salvador', 'Salvatore', 'Sam', 'Samantha', 'Sammie',
	'Sammy', 'Samuel', 'Sandi', 'Sandra', 'Sandy', 'Santiago', 'Santos',
	'Sara', 'Sarah', 'Sasha', 'Saul', 'Saundra', 'Scot', 'Scott', 'Scottie',
	'Scotty', 'Sean', 'Sebastian', 'Sedrick', 'Selena', 'Selina', 'Seneca',
	'Serena', 'Sergio', 'Seth', 'Shad', 'Shalonda', 'Shameka', 'Shamika',
	'Shana', 'Shanda', 'Shane', 'Shani', 'Shanika', 'Shanna', 'Shannan',
	'Shannon', 'Shanon', 'Shanta', 'Shantel', 'Shantell', 'Shara', 'Shari',
	'Sharla', 'Sharon', 'Sharonda', 'Sharron', 'Shaun', 'Shauna', 'Shawanda',
	'Shawn', 'Shawna', 'Shay', 'Shayla', 'Shayne', 'Shea', 'Sheila', 'Shelby',
	'Sheldon', 'Shelia', 'Shelley', 'Shelli', 'Shellie', 'Shelly', 'Shelton',
	'Sheree', 'Sheri', 'Sherita', 'Sherman', 'Sherri', 'Sherrie', 'Sherry',
	'Sheryl', 'Shirley', 'Shon', 'Shonda', 'Shonna', 'Sidney', 'Silas',
	'Silvia', 'Simon', 'Simone', 'Sofia', 'Solomon', 'Sommer', 'Sondra',
	'Sonia', 'Sonja', 'Sonny', 'Sonya', 'Sophia', 'Spencer', 'Spring',
	'Stacey', 'Staci', 'Stacia', 'Stacie', 'Stacy', 'Stanley', 'Starla',
	'Stefan', 'Stefanie', 'Stella', 'Stephan', 'Stephanie', 'Stephen',
	'Stephenie', 'Sterling', 'Steve', 'Steven', 'Stevie', 'Stewart',
	'Stuart', 'Sue', 'Summer', 'Sunny', 'Sunshine', 'Susan', 'Susana',
	'Susanna', 'Susannah', 'Susanne', 'Susie', 'Suzanna', 'Suzanne',
	'Suzette', 'Sylvester', 'Sylvia', 'Tabatha', 'Tabitha', 'Tad',
	'Takisha', 'Tamara', 'Tamatha', 'Tameka', 'Tamela', 'Tamera', 'Tami',
	'Tamica', 'Tamika', 'Tamiko', 'Tammi', 'Tammie', 'Tammy', 'Tamra',
	'Tana', 'Tanesha', 'Tangela', 'Tania', 'Tanika', 'Tanisha', 'Tanner',
	'Tanya', 'Tara', 'Tarik', 'Tarsha', 'Taryn', 'Tasha', 'Tate', 'Taurus',
	'Tavares', 'Tawana', 'Tawanda', 'Tawanna', 'Tawnya', 'Taylor', 'Ted',
	'Teddy', 'Telly', 'Tennille', 'Tera', 'Terence', 'Teresa', 'Teri',
	'Terra', 'Terrance', 'Terrell', 'Terrence', 'Terri', 'Terrie', 'Terrill',
	'Terry', 'Tessa', 'Thad', 'Thaddeus', 'Thelma', 'Theodore', 'Theresa',
	'Therese', 'Theron', 'Thomas', 'Thurman', 'Tia', 'Tiana', 'Tiffani',
	'Tiffanie', 'Tiffany', 'Tim', 'Timmothy', 'Timmy', 'Timothy', 'Tina',
	'Tisha', 'Titus', 'Tobias', 'Tobin', 'Toby', 'Tod', 'Todd', 'Tom',
	'Tomas', 'Tomeka', 'Tomika', 'Tommie', 'Tommy', 'Toni', 'Tonia', 'Tonja',
	'Tony', 'Tonya', 'Tori', 'Torrance', 'Torrence', 'Torrey', 'Tory',
	'Tosha', 'Toya', 'Tracey', 'Traci', 'Tracie', 'Tracy', 'Travis',
	'Tremayne', 'Trena', 'Trent', 'Trenton', 'Tressa', 'Trever', 'Trevor',
	'Trey', 'Tricia', 'Trina', 'Trinity', 'Trisha', 'Trista', 'Tristan',
	'Troy', 'Trudy', 'Ty', 'Tyler', 'Tyra', 'Tyree', 'Tyron', 'Tyrone',
	'Tyson', 'Ulysses', 'Ursula', 'Valarie', 'Valencia', 'Valeria',
	'Valerie', 'Van', 'Vance', 'Vanessa', 'Vaughn', 'Venus', 'Vera',
	'Vernon', 'Veronica', 'Vicente', 'Vicki', 'Vickie', 'Vicky', 'Victor',
	'Victoria', 'Vikki', 'Vince', 'Vincent', 'Virgil', 'Virginia', 'Vito',
	'Vivian', 'Wade', 'Wallace', 'Walter', 'Wanda', 'Warren', 'Waylon',
	'Wayne', 'Wendell', 'Wendi', 'Wendy', 'Wesley', 'Weston', 'Whitney',
	'Wilbert', 'Wilbur', 'Wilfred', 'Wilfredo', 'Will', 'Willard', 'William',
	'Willie', 'Willis', 'Wilson', 'Windy', 'Winston', 'Woodrow', 'Wyatt',
	'Xavier', 'Yesenia', 'Yolanda', 'Yolonda', 'Yvette', 'Yvonne',
	'Zachariah', 'Zachary', 'Zachery', 'Zane'
);

$last_names = array(
	'Abbott', 'Acevedo', 'Acosta', 'Adams', 'Adkins', 'Aguilar', 'Aguirre',
	'Alexander', 'Ali', 'Allen', 'Allison', 'Alvarado', 'Alvarez', 'Andersen',
	'Anderson', 'Andrade', 'Andrews', 'Anthony', 'Archer', 'Arellano', 'Arias',
	'Armstrong', 'Arnold', 'Arroyo', 'Ashley', 'Atkins', 'Atkinson', 'Austin',
	'Avery', 'Avila', 'Ayala', 'Ayers', 'Bailey', 'Baird', 'Baker', 'Baldwin',
	'Ball', 'Ballard', 'Banks', 'Barajas', 'Barber', 'Barker', 'Barnes',
	'Barnett', 'Barr', 'Barrera', 'Barrett', 'Barron', 'Barry', 'Bartlett',
	'Barton', 'Bass', 'Bates', 'Bauer', 'Bautista', 'Baxter', 'Bean', 'Beard',
	'Beasley', 'Beck', 'Becker', 'Bell', 'Beltran', 'Bender', 'Benitez',
	'Benjamin', 'Bennett', 'Benson', 'Bentley', 'Benton', 'Berg', 'Berger',
	'Bernard', 'Berry', 'Best', 'Bird', 'Bishop', 'Black', 'Blackburn',
	'Blackwell', 'Blair', 'Blake', 'Blanchard', 'Blankenship', 'Blevins',
	'Bolton', 'Bond', 'Bonilla', 'Booker', 'Boone', 'Booth', 'Bowen',
	'Bowers', 'Bowman', 'Boyd', 'Boyer', 'Boyle', 'Bradford', 'Bradley',
	'Bradshaw', 'Brady', 'Branch', 'Brandt', 'Braun', 'Bray', 'Brennan',
	'Brewer', 'Bridges', 'Briggs', 'Bright', 'Brock', 'Brooks', 'Brown',
	'Browning', 'Bruce', 'Bryan', 'Bryant', 'Buchanan', 'Buck', 'Buckley',
	'Bullock', 'Burch', 'Burgess', 'Burke', 'Burnett', 'Burns', 'Burton',
	'Bush', 'Butler', 'Byrd', 'Cabrera', 'Cain', 'Calderon', 'Caldwell',
	'Calhoun', 'Callahan', 'Camacho', 'Cameron', 'Campbell', 'Campos',
	'Cannon', 'Cantrell', 'Cantu', 'Cardenas', 'Carey', 'Carlson', 'Carney',
	'Carpenter', 'Carr', 'Carrillo', 'Carroll', 'Carson', 'Carter', 'Case',
	'Casey', 'Castaneda', 'Castillo', 'Castro', 'Cervantes', 'Chambers',
	'Chan', 'Chandler', 'Chaney', 'Chang', 'Chapman', 'Charles', 'Chase',
	'Chavez', 'Chen', 'Cherry', 'Choi', 'Christensen', 'Christian', 'Chung',
	'Church', 'Cisneros', 'Clark', 'Clarke', 'Clay', 'Clayton', 'Clements',
	'Cline', 'Cobb', 'Cochran', 'Coffey', 'Cohen', 'Cole', 'Coleman',
	'Collier', 'Collins', 'Colon', 'Combs', 'Compton', 'Conley', 'Conner',
	'Conrad', 'Contreras', 'Conway', 'Cook', 'Cooke', 'Cooley', 'Cooper',
	'Copeland', 'Cordova', 'Cortez', 'Costa', 'Cowan', 'Cox', 'Craig',
	'Crane', 'Crawford', 'Crosby', 'Cross', 'Cruz', 'Cuevas', 'Cummings',
	'Cunningham', 'Curry', 'Curtis', 'Dalton', 'Daniel', 'Daniels',
	'Daugherty', 'Davenport', 'David', 'Davidson', 'Davies', 'Davila',
	'Davis', 'Dawson', 'Day', 'Dean', 'Decker', 'Delacruz', 'Deleon',
	'Delgado', 'Dennis', 'Diaz', 'Dickerson', 'Dickson', 'Dillon', 'Dixon',
	'Dodson', 'Dominguez', 'Donaldson', 'Donovan', 'Dorsey', 'Dougherty',
	'Douglas', 'Downs', 'Doyle', 'Drake', 'Duarte', 'Dudley', 'Duffy',
	'Duke', 'Duncan', 'Dunlap', 'Dunn', 'Duran', 'Durham', 'Dyer', 'Eaton',
	'Edwards', 'Elliott', 'Ellis', 'Ellison', 'English', 'Erickson',
	'Escobar', 'Esparza', 'Espinoza', 'Estes', 'Estrada', 'Evans', 'Everett',
	'Ewing', 'Farley', 'Farmer', 'Farrell', 'Faulkner', 'Ferguson',
	'Fernandez', 'Ferrell', 'Fields', 'Figueroa', 'Finley', 'Fischer',
	'Fisher', 'Fitzgerald', 'Fitzpatrick', 'Fleming', 'Fletcher', 'Flores',
	'Flowers', 'Floyd', 'Flynn', 'Foley', 'Forbes', 'Ford', 'Foster',
	'Fowler', 'Fox', 'Francis', 'Franco', 'Frank', 'Franklin', 'Frazier',
	'Frederick', 'Freeman', 'French', 'Frey', 'Friedman', 'Fritz', 'Frost',
	'Fry', 'Frye', 'Fuentes', 'Fuller', 'Gaines', 'Gallagher', 'Gallegos',
	'Galloway', 'Galvan', 'Gamble', 'Garcia', 'Gardner', 'Garner', 'Garrett',
	'Garrison', 'Garza', 'Gates', 'Gay', 'Gentry', 'George', 'Gibbs',
	'Gibson', 'Gilbert', 'Giles', 'Gill', 'Gillespie', 'Gilmore', 'Glass',
	'Glenn', 'Glover', 'Golden', 'Gomez', 'Gonzales', 'Gonzalez', 'Good',
	'Goodman', 'Goodwin', 'Gordon', 'Gould', 'Graham', 'Grant', 'Graves',
	'Gray', 'Green', 'Greene', 'Greer', 'Gregory', 'Griffin', 'Griffith',
	'Grimes', 'Gross', 'Guerra', 'Guerrero', 'Gutierrez', 'Guzman', 'Haas',
	'Hahn', 'Hale', 'Haley', 'Hall', 'Hamilton', 'Hammond', 'Hampton',
	'Hancock', 'Haney', 'Hanna', 'Hansen', 'Hanson', 'Hardin', 'Harding',
	'Hardy', 'Harmon', 'Harper', 'Harrell', 'Harrington', 'Harris',
	'Harrison', 'Hart', 'Hartman', 'Harvey', 'Hatfield', 'Hawkins',
	'Hayden', 'Hayes', 'Haynes', 'Hays', 'Heath', 'Hebert', 'Henderson',
	'Hendricks', 'Hendrix', 'Henry', 'Hensley', 'Henson', 'Herman',
	'Hernandez', 'Herrera', 'Herring', 'Hess', 'Hester', 'Hickman', 'Hicks',
	'Higgins', 'Hill', 'Hines', 'Hinton', 'Ho', 'Hobbs', 'Hodge', 'Hodges',
	'Hoffman', 'Hogan', 'Holden', 'Holder', 'Holland', 'Holloway', 'Holmes',
	'Holt', 'Hood', 'Hooper', 'Hoover', 'Hopkins', 'Horn', 'Horne', 'Horton',
	'House', 'Houston', 'Howard', 'Howe', 'Howell', 'Huang', 'Hubbard',
	'Huber', 'Hudson', 'Huerta', 'Huff', 'Huffman', 'Hughes', 'Hull',
	'Humphrey', 'Hunt', 'Hunter', 'Hurley', 'Hurst', 'Hutchinson', 'Huynh',
	'Ibarra', 'Ingram', 'Irwin', 'Jackson', 'Jacobs', 'Jacobson', 'James',
	'Jarvis', 'Jefferson', 'Jenkins', 'Jennings', 'Jensen', 'Jimenez',
	'Johns', 'Johnson', 'Johnston', 'Jones', 'Jordan', 'Joseph', 'Joyce',
	'Juarez', 'Kaiser', 'Kane', 'Kaufman', 'Keith', 'Keller', 'Kelley',
	'Kelly', 'Kemp', 'Kennedy', 'Kent', 'Kerr', 'Key', 'Khan', 'Kidd',
	'Kim', 'King', 'Kirby', 'Kirk', 'Klein', 'Kline', 'Knapp', 'Knight',
	'Knox', 'Koch', 'Kramer', 'Krause', 'Krueger', 'Lam', 'Lamb', 'Lambert',
	'Landry', 'Lane', 'Lang', 'Lara', 'Larsen', 'Larson', 'Lawrence',
	'Lawson', 'Le', 'Leach', 'Leblanc', 'Lee', 'Leon', 'Leonard', 'Lester',
	'Levine', 'Levy', 'Lewis', 'Li', 'Lin', 'Lindsey', 'Little', 'Liu',
	'Livingston', 'Lloyd', 'Logan', 'Long', 'Lopez', 'Love', 'Lowe',
	'Lowery', 'Lozano', 'Lucas', 'Lucero', 'Luna', 'Lutz', 'Lynch', 'Lynn',
	'Lyons', 'Macdonald', 'Macias', 'Mack', 'Madden', 'Maddox', 'Mahoney',
	'Maldonado', 'Malone', 'Mann', 'Manning', 'Marks', 'Marquez', 'Marsh',
	'Marshall', 'Martin', 'Martinez', 'Mason', 'Massey', 'Mata', 'Mathews',
	'Mathis', 'Matthews', 'Maxwell', 'May', 'Mayer', 'Maynard', 'Mayo',
	'Mays', 'McBride', 'McCall', 'McCann', 'McCarthy', 'McCarty', 'McClain',
	'McClure', 'McConnell', 'McCormick', 'McCoy', 'McCullough', 'McDaniel',
	'McDonald', 'McDowell', 'McFarland', 'McGee', 'McGrath', 'McGuire',
	'McIntosh', 'McIntyre', 'McKay', 'McKee', 'McKenzie', 'McKinney',
	'McKnight', 'McLaughlin', 'McLean', 'McMahon', 'McMillan', 'McNeil',
	'McPherson', 'Meadows', 'Medina', 'Mejia', 'Melendez', 'Melton',
	'Mendez', 'Mendoza', 'Mercado', 'Mercer', 'Merritt', 'Meyer', 'Meyers',
	'Meza', 'Michael', 'Middleton', 'Miles', 'Miller', 'Mills', 'Miranda',
	'Mitchell', 'Molina', 'Monroe', 'Montes', 'Montgomery', 'Montoya',
	'Moody', 'Moon', 'Mooney', 'Moore', 'Mora', 'Morales', 'Moran', 'Moreno',
	'Morgan', 'Morris', 'Morrison', 'Morrow', 'Morse', 'Morton', 'Moses',
	'Mosley', 'Moss', 'Moyer', 'Mueller', 'Mullen', 'Mullins', 'Munoz',
	'Murillo', 'Murphy', 'Murray', 'Myers', 'Nash', 'Navarro', 'Neal',
	'Nelson', 'Newman', 'Newton', 'Nguyen', 'Nichols', 'Nicholson',
	'Nielsen', 'Nixon', 'Noble', 'Nolan', 'Norman', 'Norris', 'Norton',
	'Novak', 'Nunez', 'Obrien', 'Ochoa', 'Oconnell', 'Oconnor', 'Odom',
	'Odonnell', 'Oliver', 'Olsen', 'Olson', 'Oneal', 'Oneill', 'Orozco',
	'Orr', 'Ortega', 'Ortiz', 'Osborn', 'Osborne', 'Owen', 'Owens', 'Pace',
	'Pacheco', 'Padilla', 'Page', 'Palmer', 'Park', 'Parker', 'Parks',
	'Parrish', 'Parsons', 'Patel', 'Patrick', 'Patterson', 'Patton', 'Paul',
	'Payne', 'Pearson', 'Peck', 'Pena', 'Pennington', 'Perez', 'Perkins',
	'Perry', 'Peters', 'Petersen', 'Peterson', 'Petty', 'Pham', 'Phelps',
	'Phillips', 'Pierce', 'Pineda', 'Pittman', 'Pitts', 'Pollard', 'Ponce',
	'Poole', 'Pope', 'Porter', 'Potter', 'Potts', 'Powell', 'Powers',
	'Pratt', 'Preston', 'Price', 'Prince', 'Proctor', 'Pruitt', 'Pugh',
	'Quinn', 'Ramirez', 'Ramos', 'Ramsey', 'Randall', 'Randolph', 'Rangel',
	'Rasmussen', 'Ray', 'Raymond', 'Reed', 'Reese', 'Reeves', 'Reid',
	'Reilly', 'Reyes', 'Reynolds', 'Rhodes', 'Rice', 'Rich', 'Richard',
	'Richards', 'Richardson', 'Richmond', 'Riddle', 'Riggs', 'Riley',
	'Rios', 'Ritter', 'Rivas', 'Rivera', 'Rivers', 'Roach', 'Robbins',
	'Roberson', 'Roberts', 'Robertson', 'Robinson', 'Robles', 'Rocha',
	'Rodgers', 'Rodriguez', 'Rogers', 'Rojas', 'Rollins', 'Roman', 'Romero',
	'Rosales', 'Rosario', 'Rose', 'Ross', 'Roth', 'Rowe', 'Rowland', 'Roy',
	'Rubio', 'Ruiz', 'Rush', 'Russell', 'Russo', 'Ryan', 'Salas', 'Salazar',
	'Salinas', 'Sampson', 'Sanchez', 'Sanders', 'Sandoval', 'Sanford',
	'Santana', 'Santiago', 'Santos', 'Saunders', 'Savage', 'Sawyer',
	'Schaefer', 'Schmidt', 'Schmitt', 'Schneider', 'Schroeder', 'Schultz',
	'Schwartz', 'Scott', 'Sellers', 'Serrano', 'Sexton', 'Shaffer', 'Shah',
	'Shannon', 'Sharp', 'Shaw', 'Shea', 'Shelton', 'Shepard', 'Shepherd',
	'Sheppard', 'Sherman', 'Shields', 'Short', 'Silva', 'Simmons', 'Simon',
	'Simpson', 'Sims', 'Singh', 'Singleton', 'Skinner', 'Sloan', 'Small',
	'Smith', 'Snow', 'Snyder', 'Solis', 'Solomon', 'Sosa', 'Soto', 'Sparks',
	'Spears', 'Spence', 'Spencer', 'Stafford', 'Stanley', 'Stanton', 'Stark',
	'Steele', 'Stein', 'Stephens', 'Stephenson', 'Stevens', 'Stevenson',
	'Stewart', 'Stokes', 'Stone', 'Stout', 'Strickland', 'Strong', 'Stuart',
	'Suarez', 'Sullivan', 'Summers', 'Sutton', 'Swanson', 'Sweeney',
	'Tanner', 'Tapia', 'Tate', 'Taylor', 'Terrell', 'Terry', 'Thomas',
	'Thompson', 'Thornton', 'Todd', 'Torres', 'Townsend', 'Tran', 'Travis',
	'Trevino', 'Trujillo', 'Tucker', 'Turner', 'Tyler', 'Underwood',
	'Valdez', 'Valencia', 'Valentine', 'Valenzuela', 'Vance', 'Vang',
	'Vargas', 'Vasquez', 'Vaughan', 'Vaughn', 'Vazquez', 'Vega', 'Velasquez',
	'Velazquez', 'Velez', 'Villa', 'Villanueva', 'Villarreal', 'Villegas',
	'Vincent', 'Wade', 'Wagner', 'Walker', 'Wall', 'Wallace', 'Waller',
	'Walls', 'Walsh', 'Walter', 'Walters', 'Walton', 'Wang', 'Ward', 'Ware',
	'Warner', 'Warren', 'Washington', 'Waters', 'Watkins', 'Watson',
	'Watts', 'Weaver', 'Webb', 'Weber', 'Webster', 'Weeks', 'Weiss', 'Welch',
	'Wells', 'Werner', 'West', 'Wheeler', 'Whitaker', 'White', 'Whitehead',
	'Whitney', 'Wiggins', 'Wilcox', 'Wiley', 'Wilkerson', 'Wilkins',
	'Wilkinson', 'Williams', 'Williamson', 'Willis', 'Wilson', 'Winters',
	'Wise', 'Wolf', 'Wolfe', 'Wong', 'Wood', 'Woodard', 'Woods', 'Woodward',
	'Wright', 'Wu', 'Wyatt', 'Yang', 'Yates', 'Yoder', 'York', 'Young',
	'Yu', 'Zamora', 'Zavala', 'Zhang', 'Zimmerman', 'Zuniga'
);

$fandom_names = array(
	'Apple Bloom', 'Applejack', 'Babs Seed', 'Big Macintosh', 'Carrot Cake',
	'Cheerilee', 'Cup Cake', 'Derpy Hooves', 'Diamond Tiara', 'Discord',
	'Fluttershy', 'Gilda', 'Granny Smith', 'Joe', 'Kara Music', 'Luckette',
	'Mayor Mare', 'Octavia', 'Pinkie Pie', 'Pound Cake', 'Princess Cadence',
	'Princess Celestia', 'Princess Luna', 'Pumpkin Cake', 'Rainbow Dash',
	'Rarity', 'Scootaloo', 'Shining Armor', 'Silver Spoon', 'Snails', 'Snips',
	'Soarin', 'Spike', 'Spitfire', 'Starswirl the Bearded', 'Sunset Shimmer',
	'Sweetie Belle', 'Twilight Sparkle', 'Vinyl Scratch', 'Zecora'
);

$names_on_badge = array(
	'Fandom Name Large, Real Name Small',
	'Real Name Large, Fandom Name Small',
	'Fandom Name Only', 'Real Name Only'
);

$street_names = array(
	'1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th',
	'11th', '12th', '13th', 'Airport', 'Aloha', 'Apache', 'Aspen', 'Bay',
	'Birch', 'Broadway', 'Canyon', 'Cedar', 'Center', 'Cherry', 'Church',
	'Columbine', 'Cottonwood', 'County Line', 'Cypress', 'Delaware',
	'Dogwood', 'Elm', 'Evergreen', 'Hampton', 'Hemlock', 'Hickory',
	'Highland', 'Hill', 'Hillside', 'Holly', 'Jackson', 'Johnson',
	'Juniper', 'Kahili', 'Kansas', 'Kukui', 'Lake', 'Lakeview', 'Laurel',
	'Lee', 'Lehua', 'Lincoln', 'Magnolia', 'Main', 'Malulani', 'Maple',
	'Meadow', 'Mesquite', 'Mountain View', 'Narragansett', 'Navajo',
	'North', 'Oak', 'Orchard', 'Palo Verde', 'Park', 'Pecan', 'Pine',
	'Pinon', 'Pioneer', 'Pleasant', 'Quail', 'Redwood', 'Ridge', 'River',
	'Shore', 'Smith', 'Spruce', 'Sunset', 'Sycamore', 'Walnut', 'Washington',
	'West', 'Williams', 'Willow', 'Wilson', 'Wood'
);

$street_name_suffixes = array(
	'Alley', 'Annex', 'Arcade', 'Avenue', 'Bayou', 'Beach', 'Bend', 'Bluff',
	'Bluffs', 'Bottom', 'Boulevard', 'Branch', 'Bridge', 'Brook', 'Brooks',
	'Burg', 'Burgs', 'Bypass', 'Camp', 'Canyon', 'Cape', 'Causeway', 'Center',
	'Centers', 'Circle', 'Circles', 'Cliff', 'Cliffs', 'Club', 'Common',
	'Commons', 'Corner', 'Corners', 'Course', 'Court', 'Courts', 'Cove',
	'Coves', 'Creek', 'Crescent', 'Crest', 'Crossing', 'Crossroad',
	'Crossroads', 'Curve', 'Dale', 'Dam', 'Divide', 'Drive', 'Drives',
	'Estate', 'Estates', 'Expressway', 'Extension', 'Extensions', 'Fall',
	'Falls', 'Ferry', 'Field', 'Fields', 'Flat', 'Flats', 'Ford', 'Fords',
	'Forest', 'Forge', 'Forges', 'Fork', 'Forks', 'Fort', 'Freeway',
	'Garden', 'Gardens', 'Gateway', 'Glen', 'Glens', 'Green', 'Greens',
	'Grove', 'Groves', 'Harbor', 'Harbors', 'Haven', 'Heights', 'Highway',
	'Hill', 'Hills', 'Hollow', 'Inlet', 'Island', 'Islands', 'Isle',
	'Junction', 'Junctions', 'Key', 'Keys', 'Knoll', 'Knolls', 'Lake',
	'Lakes', 'Land', 'Landing', 'Lane', 'Light', 'Lights', 'Loaf', 'Lock',
	'Locks', 'Lodge', 'Loop', 'Mall', 'Manor', 'Manors', 'Meadow',
	'Meadows', 'Mews', 'Mill', 'Mills', 'Mission', 'Motorway', 'Mount',
	'Mountain', 'Mountains', 'Neck', 'Orchard', 'Oval', 'Overpass', 'Park',
	'Parks', 'Parkway', 'Parkways', 'Pass', 'Passage', 'Path', 'Pike', 'Pine',
	'Pines', 'Place', 'Plain', 'Plains', 'Plaza', 'Point', 'Points', 'Port',
	'Ports', 'Prairie', 'Radial', 'Ramp', 'Ranch', 'Rapid', 'Rapids', 'Rest',
	'Ridge', 'Ridges', 'River', 'Road', 'Roads', 'Route', 'Row', 'Rue', 'Run',
	'Shoal', 'Shoals', 'Shore', 'Shores', 'Skyway', 'Spring', 'Springs',
	'Spur', 'Spurs', 'Square', 'Squares', 'Station', 'Stravenue', 'Stream',
	'Street', 'Streets', 'Summit', 'Terrace', 'Throughway', 'Trace', 'Track',
	'Trafficway', 'Trail', 'Trailer', 'Tunnel', 'Turnpike', 'Underpass',
	'Union', 'Unions', 'Valley', 'Valleys', 'Viaduct', 'View', 'Views',
	'Village', 'Villages', 'Ville', 'Vista', 'Walk', 'Walks', 'Wall', 'Way',
	'Ways', 'Well', 'Wells'
);

$unit_suffixes = array(
	'Apartment', 'Building', 'Department', 'Floor', 'Hangar', 'Lot', 'Pier',
	'Room', 'Slip', 'Space', 'Stop', 'Suite', 'Trailer', 'Unit'
);

$cities = array(
	'Springfield', 'Clinton', 'Madison', 'Franklin', 'Washington',
	'Chester', 'Marion', 'Greenville', 'Georgetown', 'Salem'
);

$states = array(
	'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
	'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
	'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
	'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
	'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
	'New Hampshire', 'New Jersey', 'New Mexico', 'New York',
	'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon',
	'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
	'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
	'West Virginia', 'Wisconsin', 'Wyoming'
);

$relationships = array(
	'Mother', 'Father', 'Son', 'Daughter', 'Sister', 'Brother', 'Cousin',
	'Wife', 'Husband', 'Aunt', 'Uncle', 'Niece', 'Nephew', 'Friend'
);

$payment_statuses = array(
	'Incomplete', 'Cancelled', 'Rejected', 'Completed', 'Refunded'
);

$alphabet = array(
	'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B',
	'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
	'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
);

function random_string() {
	global $alphabet;
	$str = $alphabet[rand(0, count($alphabet) - 1)];
	for ($i = 0, $n = rand(0, 19); $i < $n; $i++) {
		$str .= $alphabet[rand(0, count($alphabet) - 1)];
	}
	return $str;
}

function random_attendee() {
	global $db, $atdb, $badge_type_ids, $notes, $first_names, $last_names;
	global $fandom_names, $names_on_badge, $street_names, $street_name_suffixes;
	global $unit_suffixes, $cities, $states, $relationships, $payment_statuses;
	$badge_type_id = $badge_type_ids[rand(0, count($badge_type_ids) - 1)];
	$note = $notes[rand(0, count($notes) - 1)];
	for ($i = 0, $n = rand(0, 19); $i < $n; $i++) {
		$note .= ' ' . $notes[rand(0, count($notes) - 1)];
	}
	$first_name = $first_names[rand(0, count($first_names) - 1)];
	$last_name = $last_names[rand(0, count($last_names) - 1)];
	$fandom_name = $fandom_names[rand(0, count($fandom_names) - 1)] . ' ' . rand(0, 9999);
	$name_on_badge = $names_on_badge[rand(0, count($names_on_badge) - 1)];
	$date_of_birth = rand(1940, 2015) . '-' . rand(1, 12) . '-' . rand(1, 28);
	$subscribed = !rand(0, 1);
	$email_address = strtolower($first_name) . '@' . strtolower($last_name) . '.name';
	$phone_number = rand(100, 999) . '-' . rand(100, 999) . '-' . rand(1000, 9999);
	$address_1 = rand(1, 9999) . ' ' . $street_names[rand(0, count($street_names) - 1)] . ' ' .
		$street_name_suffixes[rand(0, count($street_name_suffixes) - 1)];
	$address_2 = $unit_suffixes[rand(0, count($unit_suffixes) - 1)] . ' ' . rand(1, 9999);
	$city = $cities[rand(0, count($cities) - 1)];
	$state = $states[rand(0, count($states) - 1)];
	$zip_code = rand(10000, 99999);
	$country = 'USA';
	$ice_name = $first_names[rand(0, count($first_names) - 1)] . ' ' .
		$last_names[rand(0, count($last_names) - 1)];
	$ice_relationship = $relationships[rand(0, count($relationships) - 1)];
	$ice_email_address = str_replace(' ', '@', strtolower($ice_name)) . '.name';
	$ice_phone_number = rand(100, 999) . '-' . rand(100, 999) . '-' . rand(1000, 9999);
	$payment_status = $payment_statuses[rand(0, count($payment_statuses) - 1)];
	$payment_badge_price = rand(0, 9999) / 100;
	$payment_promo_code = random_string();
	$payment_promo_price = rand(0, 9999) / 100;
	$payment_group_uuid = $db->uuid();
	$payment_type = 'PayPal';
	$payment_txn_id = random_string();
	$payment_txn_amt = rand(0, 9999) / 100;
	$payment_date = rand(2014, 2016) . '-' . rand(1, 12) . '-' . rand(1, 28) . ' ' . rand(0, 23) . ':' . rand(0, 59) . ':' . rand(0, 59);
	$payment_details = '{"'.random_string().'":"'.random_string().'"}';
	return array(
		'badge-type-id' => $badge_type_id,
		'notes' => $note,
		'first-name' => $first_name,
		'last-name' => $last_name,
		'fandom-name' => $fandom_name,
		'name-on-badge' => $name_on_badge,
		'date-of-birth' => $date_of_birth,
		'subscribed' => $subscribed,
		'email-address' => $email_address,
		'phone-number' => $phone_number,
		'address-1' => $address_1,
		'address-2' => $address_2,
		'city' => $city,
		'state' => $state,
		'zip-code' => $zip_code,
		'country' => $country,
		'ice-name' => $ice_name,
		'ice-relationship' => $ice_relationship,
		'ice-email-address' => $ice_email_address,
		'ice-phone-number' => $ice_phone_number,
		'payment-status' => $payment_status,
		'payment-badge-price' => $payment_badge_price,
		'payment-promo-code' => $payment_promo_code,
		'payment-promo-price' => $payment_promo_price,
		'payment-group-uuid' => $payment_group_uuid,
		'payment-type' => $payment_type,
		'payment-txn-id' => $payment_txn_id,
		'payment-txn-amt' => $payment_txn_amt,
		'payment-date' => $payment_date,
		'payment-details' => $payment_details
	);
}

cm_admin_head('Debug');
cm_admin_body('Debug');
cm_admin_nav('debug');

echo '<article>';

if (isset($_POST['generate-randoms'])) {
	$n = (int)$_POST['generate-randoms'];
	for ($i = 0; $i < $n; $i++) {
		$a = random_attendee();
		$id = $atdb->create_attendee($a);
		echo $id . "\n";
	}
	echo '<br>';
} else {
	$n = 100;
}
echo '<form action="debug.php" method="post">';
echo '<p><label>Generate random attendees:</label> <input type="number" name="generate-randoms" value="'.$n.'"></p>';
echo '<p><input type="submit" name="submit" value="Submit"></p>';
echo '</form>';

if (isset($_POST['slack-path']) && isset($_POST['slack-message'])) {
	$slack = new cm_slack();
	$slack->post_message($_POST['slack-path'], $_POST['slack-message']);
}
echo '<form action="debug.php" method="post">';
echo '<p><label>Slack Config Path:</label> <input type="text" name="slack-path"></p>';
echo '<p><label>Slack Message:</label> <input type="text" name="slack-message"></p>';
echo '<p><input type="submit" name="submit" value="Submit"></p>';
echo '</form>';

echo '</article>';

cm_admin_dialogs();
cm_admin_tail();